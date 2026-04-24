<?php

namespace App\Console\Commands;

use App\Mail\CampaignMail;
use App\Models\EmailQueue;
use App\Models\SmtpServer;
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

        $items = EmailQueue::with(['campaign', 'contact'])
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere(function ($q) {
                        $q->where('status', 'failed')
                          ->where('attempts', '<', 3);
                    });
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($items as $item) {
            $isUnsubscribed = Unsubscribe::whereRaw('LOWER(email) = ?', [strtolower($item->email)])->exists();

            if ($isUnsubscribed) {
                $item->update([
                    'status' => 'failed',
                    'attempts' => 0,
                    'last_error' => 'Unsubscribed',
                ]);

                $this->warn("Skipped unsubscribed email: {$item->email}");
                continue;
            }

            $smtp = SmtpServer::active()
                ->orderBy('last_used_at')
                ->orderBy('id')
                ->first();

            if (!$smtp) {
                $this->warn('No active SMTP servers found.');
                break;
            }

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $smtp->host,
                'mail.mailers.smtp.port' => $smtp->port,
                'mail.mailers.smtp.encryption' => $smtp->encryption === 'none' ? null : $smtp->encryption,
                'mail.mailers.smtp.username' => $smtp->username,
                'mail.mailers.smtp.password' => $smtp->password,
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
            } catch (\Throwable $e) {
                $attempts = $item->attempts + 1;
                $item->update([
                    'attempts' => $attempts,
                    'status' => 'failed',
                    'last_error' => $e->getMessage(),
                ]);
            }

            $smtp->update(['last_used_at' => Carbon::now()]);
            $processed++;
            sleep(1);
        }

        $this->info("Processed {$processed} email(s).");
        return self::SUCCESS;
    }
}
