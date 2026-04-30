<?php

namespace App\Console\Commands;

use App\Mail\CampaignMail;
use App\Models\EmailQueue;
use App\Models\SmtpServer;
use App\Models\SmtpServerUsage;
use App\Models\Unsubscribe;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class WorkMailsQueueCommand extends Command
{
    protected $signature = 'queue:work-mails {--limit=60}';
    protected $description = 'Process pending campaign emails with SMTP rotation and retries';

    public function handle()
    {
        $limit = (int) $this->option('limit');
        $processed = 0;

        $allItems = EmailQueue::with(['campaign', 'contact'])
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere(function ($q) {
                        $q->where('status', 'failed')
                          ->where('attempts', '<', 3);
                    });
            })
            ->orderBy('id')
            ->get();

        $campaignBuckets = $allItems->groupBy('campaign_id');
        $items = collect();

        foreach ($campaignBuckets as $campaignId => $bucket) {
            $campaign = $bucket->first()?->campaign;

            if (!$campaign) {
                continue;
            }

            if ($campaign->warmup_enabled) {
                $campaign->syncWarmupProgress();
                $cap = $campaign->currentWarmupCap();
                $selected = $bucket->take($cap);
                $this->line("Warmup cap applied for campaign #{$campaign->id}: day={$campaign->getEffectiveWarmupDay()}, cap={$cap}, selected={$selected->count()}");
            } else {
                $selected = $bucket;
            }

            $items = $items->merge($selected);
        }

        $items = $items->sortBy('id')->take($limit)->values();

        foreach ($items as $item) {
            $this->line("Processing queue #{$item->id} | campaign #{$item->campaign_id} | {$item->email} | status={$item->status} attempts={$item->attempts}");

            $isUnsubscribed = Unsubscribe::whereRaw('LOWER(email) = ?', [strtolower($item->email)])->exists();

            if ($isUnsubscribed) {
                $item->update([
                    'status' => 'failed',
                    'attempts' => 3,
                    'last_error' => 'Unsubscribed',
                ]);

                $this->warn("Skipped unsubscribed email: {$item->email}");
                continue;
            }

            $accountId = (int) ($item->campaign?->account_id ?? 0);
            if ($accountId <= 0) {
                $item->update([
                    'attempts' => $item->attempts + 1,
                    'status' => 'failed',
                    'last_error' => 'Missing account context',
                ]);
                $this->error("Queue #{$item->id} failed: missing account context.");
                continue;
            }

            $smtpServers = SmtpServer::forAccount($accountId)
                ->active()
                ->orderBy('priority')
                ->orderBy('last_used_at')
                ->orderBy('id')
                ->get();

            if ($smtpServers->isEmpty()) {
                $this->warn("No active SMTP servers found for account #{$accountId}.");
                break;
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
                    Mail::to($item->email)->send(new CampaignMail($item->campaign, $item->contact, $item->id));

                    $item->update([
                        'status' => 'sent',
                        'sent_at' => Carbon::now(),
                        'last_error' => null,
                    ]);

                    $smtp->update(['last_used_at' => Carbon::now()]);

                    $usage = SmtpServerUsage::query()->firstOrCreate(
                        [
                            'smtp_server_id' => $smtp->id,
                            'account_id' => $accountId,
                            'usage_date' => $today,
                        ],
                        [
                            'sent_count' => 0,
                            'fail_count' => 0,
                        ]
                    );
                    $usage->increment('sent_count');

                    $this->info("Sent successfully: {$item->email}");
                    $sent = true;
                    break;
                } catch (\Throwable $e) {
                    $lastError = $e->getMessage();
                    $smtp->update(['last_used_at' => Carbon::now()]);

                    $usage = SmtpServerUsage::query()->firstOrCreate(
                        [
                            'smtp_server_id' => $smtp->id,
                            'account_id' => $accountId,
                            'usage_date' => $today,
                        ],
                        [
                            'sent_count' => 0,
                            'fail_count' => 0,
                        ]
                    );
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

            $processed++;
            sleep(1);
        }

        $campaignIds = $items->pluck('campaign_id')->unique()->filter()->values();

        foreach ($campaignIds as $campaignId) {
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
                \App\Models\Campaign::where('id', $campaignId)->update(['status' => 'sending']);
                $this->line("Campaign #{$campaignId} status => sending");
            } elseif ($totalCount > 0 && $sentCount === $totalCount) {
                \App\Models\Campaign::where('id', $campaignId)->update(['status' => 'completed']);
                $this->line("Campaign #{$campaignId} status => completed");
            } elseif ($totalCount > 0 && $failedCount === $totalCount) {
                \App\Models\Campaign::where('id', $campaignId)->update(['status' => 'paused']);
                $this->line("Campaign #{$campaignId} status => paused (all queue rows failed)");
            } elseif ($totalCount > 0 && $sentCount > 0) {
                \App\Models\Campaign::where('id', $campaignId)->update(['status' => 'completed']);
                $this->line("Campaign #{$campaignId} status => completed (partial success, no sendable rows)");
            } else {
                \App\Models\Campaign::where('id', $campaignId)->update(['status' => 'scheduled']);
                $this->line("Campaign #{$campaignId} status => scheduled (no sendable rows)");
            }
        }

        $this->info("Processed {$processed} email(s).");
        return self::SUCCESS;
    }
}
