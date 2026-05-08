<?php

namespace App\Console\Commands;

use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\EmailQueue;
use App\Models\SmtpServer;
use App\Models\SmtpServerUsage;
use App\Models\Unsubscribe;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class WorkMailsQueueCommand extends Command
{
    protected $signature = 'queue:work-mails {--limit=60} {--campaign_id=}';
    protected $description = 'Process pending campaign emails with per-campaign throttling, fair distribution, SMTP rotation and retries';

    public function handle()
    {
        $fallbackLimit = max(1, (int) $this->option('limit'));
        $campaignIdOption = $this->option('campaign_id');
        $targetCampaignId = is_null($campaignIdOption) ? null : (int) $campaignIdOption;

        $processed = 0;
        $processedPerCampaign = [];
        $windowStartedAt = microtime(true);

        if (!is_null($targetCampaignId) && $targetCampaignId <= 0) {
            $this->warn('Invalid campaign_id provided. Nothing to process.');
            return self::SUCCESS;
        }

        $items = EmailQueue::with(['campaign', 'contact'])
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere(function ($q) {
                        $q->where('status', 'failed')
                            ->where('attempts', '<', 3);
                    });
            })
            ->when(!is_null($targetCampaignId), function ($query) use ($targetCampaignId) {
                $query->where('campaign_id', $targetCampaignId);
            })
            ->orderBy('id')
            ->get();

        if ($items->isEmpty()) {
            $this->info('No sendable queue items found.');
            return self::SUCCESS;
        }

        $campaignBuckets = $items->groupBy('campaign_id');
        $orderedBuckets = [];
        $campaignRunCaps = [];

        foreach ($campaignBuckets as $campaignId => $bucket) {
            $campaign = $bucket->first()?->campaign;

            if (!$campaign) {
                continue;
            }

            if (!is_null($targetCampaignId)) {
                if ((int) $campaign->id !== $targetCampaignId) {
                    continue;
                }

                if ((string) $campaign->status !== 'sending') {
                    $this->line("Campaign #{$campaign->id} skipped in campaign mode: status={$campaign->status} (must be sending).");
                    continue;
                }
            }

            $effectiveCap = $this->resolveEffectiveCap($campaign, $fallbackLimit);
            if ($effectiveCap <= 0) {
                $this->line("Campaign #{$campaign->id} skipped: invalid effective cap {$effectiveCap}.");
                continue;
            }

            $selected = $bucket->take($effectiveCap)->values();

            if ($selected->isEmpty()) {
                continue;
            }

            $orderedBuckets[(int) $campaign->id] = $selected;
            $campaignRunCaps[(int) $campaign->id] = $effectiveCap;
            $processedPerCampaign[(int) $campaign->id] = 0;

            $this->line("Campaign #{$campaign->id} selected={$selected->count()} effective_cap={$effectiveCap}");
        }

        if (empty($orderedBuckets)) {
            $this->info('No eligible campaigns to process.');
            return self::SUCCESS;
        }

        if (!is_null($targetCampaignId)) {
            $campaignId = (int) $targetCampaignId;
            $bucket = $orderedBuckets[$campaignId] ?? collect();

            foreach ($bucket as $item) {
                $sent = $this->processQueueItem($item);
                $processed++;
                $processedPerCampaign[$campaignId] = ($processedPerCampaign[$campaignId] ?? 0) + 1;

                $this->throttleByRate($windowStartedAt, $processedPerCampaign[$campaignId], $campaignRunCaps[$campaignId]);

                if (!$sent) {
                    continue;
                }
            }
        } else {
            $activeCampaignIds = array_keys($orderedBuckets);
            $cursorByCampaign = array_fill_keys($activeCampaignIds, 0);
            $remaining = true;

            while ($remaining) {
                $remaining = false;

                foreach ($activeCampaignIds as $campaignId) {
                    /** @var Collection $bucket */
                    $bucket = $orderedBuckets[$campaignId];
                    $idx = $cursorByCampaign[$campaignId] ?? 0;

                    if ($idx >= $bucket->count()) {
                        continue;
                    }

                    $remaining = true;
                    $item = $bucket[$idx];
                    $cursorByCampaign[$campaignId] = $idx + 1;

                    $this->processQueueItem($item);
                    $processed++;
                    $processedPerCampaign[$campaignId] = ($processedPerCampaign[$campaignId] ?? 0) + 1;

                    $this->throttleByRate($windowStartedAt, $processedPerCampaign[$campaignId], $campaignRunCaps[$campaignId]);
                }
            }
        }

        $finalCampaignIds = collect(array_keys($processedPerCampaign))->filter()->values();

        foreach ($finalCampaignIds as $campaignId) {
            $campaignQueue = EmailQueue::where('campaign_id', $campaignId);

            $hasSendable = (clone $campaignQueue)
                ->where(function ($query) {
                    $query->where('status', 'pending')
                        ->orWhere(function ($q) {
                            $q->where('status', 'failed')->where('attempts', '<', 3);
                        });
                })
                ->exists();

            $sentCount = (clone $campaignQueue)->where('status', 'sent')->count();
            $totalCount = (clone $campaignQueue)->count();
            $failedCount = (clone $campaignQueue)->where('status', 'failed')->count();

            if ($hasSendable) {
                Campaign::where('id', $campaignId)->update(['status' => 'sending']);
                $this->line("Campaign #{$campaignId} status => sending");
            } elseif ($totalCount > 0 && $sentCount === $totalCount) {
                Campaign::where('id', $campaignId)->update(['status' => 'completed']);
                $this->line("Campaign #{$campaignId} status => completed");
            } elseif ($totalCount > 0 && $failedCount === $totalCount) {
                Campaign::where('id', $campaignId)->update(['status' => 'paused']);
                $this->line("Campaign #{$campaignId} status => paused (all queue rows failed)");
            } elseif ($totalCount > 0 && $sentCount > 0) {
                Campaign::where('id', $campaignId)->update(['status' => 'completed']);
                $this->line("Campaign #{$campaignId} status => completed (partial success, no sendable rows)");
            } else {
                Campaign::where('id', $campaignId)->update(['status' => 'scheduled']);
                $this->line("Campaign #{$campaignId} status => scheduled (no sendable rows)");
            }
        }

        foreach ($processedPerCampaign as $campaignId => $count) {
            $this->line("Run summary: campaign_id={$campaignId} processed={$count}");
        }

        $this->info("Processed {$processed} email(s).");
        return self::SUCCESS;
    }

    private function resolveEffectiveCap(Campaign $campaign, int $fallbackLimit): int
    {
        $speedCap = $campaign->emails_per_minute;

        if (!is_null($speedCap)) {
            $speedCap = (int) $speedCap;
            if ($speedCap <= 0) {
                return 0;
            }
        }

        $warmupCap = null;
        if ($campaign->warmup_enabled) {
            $campaign->syncWarmupProgress();
            $warmupCap = max(1, (int) $campaign->currentWarmupCap());
        }

        $effective = null;

        if (!is_null($speedCap) && !is_null($warmupCap)) {
            $effective = min($speedCap, $warmupCap);
        } elseif (!is_null($speedCap)) {
            $effective = $speedCap;
        } elseif (!is_null($warmupCap)) {
            $effective = $warmupCap;
        } else {
            $effective = $fallbackLimit;
        }

        return max(0, (int) $effective);
    }

    private function throttleByRate(float $windowStartedAt, int $processedInWindow, int $emailsPerMinute): void
    {
        if ($emailsPerMinute <= 0 || $processedInWindow <= 0) {
            return;
        }

        $expectedSeconds = ($processedInWindow / $emailsPerMinute) * 60;
        $elapsedSeconds = microtime(true) - $windowStartedAt;
        $sleepSeconds = $expectedSeconds - $elapsedSeconds;

        if ($sleepSeconds > 0) {
            usleep((int) round($sleepSeconds * 1000000));
        }
    }

    private function processQueueItem(EmailQueue $item): bool
    {
        $this->line("Processing queue #{$item->id} | campaign #{$item->campaign_id} | {$item->email} | status={$item->status} attempts={$item->attempts}");

        $isUnsubscribed = Unsubscribe::whereRaw('LOWER(email) = ?', [strtolower($item->email)])->exists();

        if ($isUnsubscribed) {
            $item->update([
                'status' => 'failed',
                'attempts' => 3,
                'last_error' => 'Unsubscribed',
            ]);

            $this->warn("Skipped unsubscribed email: {$item->email}");
            return false;
        }

        $isBounced = \App\Models\Contact::whereRaw('LOWER(email) = ?', [strtolower($item->email)])
            ->where('is_bounced', true)->exists();

        if ($isBounced) {
            $item->update([
                'status' => 'failed',
                'attempts' => 3,
                'last_error' => 'Bounced',
            ]);

            $this->warn("Skipped bounced email: {$item->email}");
            return false;
        }

        $accountId = (int) ($item->campaign?->account_id ?? 0);

        $isSuppressed = \App\Models\SuppressionEntry::where('account_id', $accountId)
            ->whereRaw('LOWER(email) = ?', [strtolower($item->email)])
            ->exists();

        if ($isSuppressed) {
            $item->update([
                'status' => 'failed',
                'attempts' => 3,
                'last_error' => 'Suppressed',
            ]);
            $this->warn("Skipped suppressed email: {$item->email}");
            return false;
        }
        if ($accountId <= 0) {
            $item->update([
                'attempts' => $item->attempts + 1,
                'status' => 'failed',
                'last_error' => 'Missing account context',
            ]);
            $this->error("Queue #{$item->id} failed: missing account context (campaign_id={$item->campaign_id}).");
            return false;
        }

        $smtpServers = SmtpServer::forAccount($accountId)
            ->active()
            ->orderBy('priority')
            ->orderBy('last_used_at')
            ->orderBy('id')
            ->get();

        if ($smtpServers->isEmpty()) {
            $this->warn("No active SMTP servers found for account #{$accountId}.");
            return false;
        }

        $sent = false;
        $lastError = null;
        $today = Carbon::today()->toDateString();

        foreach ($smtpServers as $smtp) {
            if (!is_null($smtp->daily_limit)) {
                $todaySentCount = SmtpServerUsage::query()
                    ->where('smtp_server_id', $smtp->id)
                    ->where('usage_date', $today)
                    ->value('sent_count') ?? 0;

                if ($todaySentCount >= (int) $smtp->daily_limit) {
                    $this->warn("Skipping SMTP #{$smtp->id} due to daily limit reached.");
                    continue;
                }
            }

            $this->line("Using SMTP #{$smtp->id} {$smtp->host}:{$smtp->port}");

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $smtp->host,
                'mail.mailers.smtp.port' => $smtp->port,
                'mail.mailers.smtp.encryption' => $smtp->encryption === 'none' ? null : $smtp->encryption,
                'mail.mailers.smtp.username' => $smtp->username,
                'mail.mailers.smtp.password' => $smtp->password,
                'mail.mailers.smtp.timeout' => 8,
                'mail.from.address' => $smtp->from_email,
                'mail.from.name' => $smtp->from_name,
            ]);

            try {
                Mail::to($item->email)->send(new CampaignMail($item->campaign, $item->contact, $item->id, $item->ab_variant));

                $item->update([
                    'status' => 'sent',
                    'sent_at' => Carbon::now(),
                    'last_error' => null,
                ]);

                $smtp->update(['last_used_at' => Carbon::now()]);

                $usage = SmtpServerUsage::query()
                    ->where('smtp_server_id', $smtp->id)
                    ->whereDate('usage_date', $today)
                    ->lockForUpdate()
                    ->first();

                if (!$usage) {
                    $usage = SmtpServerUsage::query()->create([
                        'smtp_server_id' => $smtp->id,
                        'usage_date' => $today,
                        'account_id' => $accountId,
                        'sent_count' => 0,
                        'fail_count' => 0,
                    ]);
                }

                if ((int) $usage->account_id !== $accountId) {
                    $usage->account_id = $accountId;
                    $usage->save();
                }

                $usage->increment('sent_count');

                $this->info("Sent successfully: {$item->email}");
                $sent = true;
                break;
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                $smtp->update(['last_used_at' => Carbon::now()]);

                $usage = SmtpServerUsage::query()
                    ->where('smtp_server_id', $smtp->id)
                    ->whereDate('usage_date', $today)
                    ->lockForUpdate()
                    ->first();

                if (!$usage) {
                    $usage = SmtpServerUsage::query()->create([
                        'smtp_server_id' => $smtp->id,
                        'usage_date' => $today,
                        'account_id' => $accountId,
                        'sent_count' => 0,
                        'fail_count' => 0,
                    ]);
                }

                if ((int) $usage->account_id !== $accountId) {
                    $usage->account_id = $accountId;
                    $usage->save();
                }

                $usage->increment('fail_count');

                $this->warn("SMTP #{$smtp->id} failed for {$item->email}: {$lastError}");
            }
        }

        if (!$sent) {
            $attempts = $item->attempts + 1;
            $item->update([
                'attempts' => $attempts,
                'status' => 'failed',
                'last_error' => $lastError ?? 'All SMTP servers failed',
            ]);

            $this->error("Send failed: {$item->email} | attempts={$attempts} | error=" . ($lastError ?? 'All SMTP servers failed'));
        }

        return $sent;
    }
}
