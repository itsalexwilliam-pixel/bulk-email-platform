<?php

namespace App\Console\Commands;

use App\Mail\DripMail;
use App\Models\Contact;
use App\Models\DripCampaign;
use App\Models\DripEnrollment;
use App\Models\DripStep;
use App\Models\EmailQueue;
use App\Models\SmtpServer;
use App\Models\SmtpServerUsage;
use App\Models\Unsubscribe;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ProcessDripsCommand extends Command
{
    protected $signature = 'queue:process-drips';
    protected $description = 'Process due drip campaign enrollments and send the next step email';

    public function handle()
    {
        $due = DripEnrollment::with(['dripCampaign.steps', 'contact'])
            ->where('status', 'active')
            ->where('next_send_at', '<=', now())
            ->whereHas('dripCampaign', fn($q) => $q->where('status', 'active'))
            ->get();

        if ($due->isEmpty()) {
            $this->info('No drip enrollments due.');
            return self::SUCCESS;
        }

        $this->info("Processing {$due->count()} due drip enrollment(s)...");

        foreach ($due as $enrollment) {
            $this->processEnrollment($enrollment);
        }

        $this->info('Drip processing complete.');
        return self::SUCCESS;
    }

    private function processEnrollment(DripEnrollment $enrollment): void
    {
        $contact = $enrollment->contact;
        $drip    = $enrollment->dripCampaign;

        if (!$contact || !$drip) {
            $enrollment->update(['status' => 'completed']);
            return;
        }

        // Skip unsubscribed / bounced / suppressed
        $isUnsubscribed = Unsubscribe::whereRaw('LOWER(email) = ?', [strtolower($contact->email)])->exists();
        $isBounced      = $contact->is_bounced ?? false;
        $isSuppressed   = \App\Models\SuppressionEntry::where('account_id', $drip->account_id)
            ->whereRaw('LOWER(email) = ?', [strtolower($contact->email)])
            ->exists();

        if ($isUnsubscribed || $isBounced || $isSuppressed) {
            $enrollment->update(['status' => 'unsubscribed']);
            $this->warn("Skipped {$contact->email} (unsubscribed/bounced/suppressed)");
            return;
        }

        // Get the step to send
        $steps      = $drip->steps->sortBy('position')->values();
        $stepIndex  = $enrollment->current_step - 1; // 0-based index
        $step       = $steps->get($stepIndex);

        if (!$step) {
            // All steps sent — mark completed
            $enrollment->update(['status' => 'completed', 'next_send_at' => null]);
            $this->line("Enrollment #{$enrollment->id} completed (all steps sent).");
            return;
        }

        $accountId = (int) $drip->account_id;

        // Pick an active SMTP server
        $smtp = SmtpServer::forAccount($accountId)
            ->active()
            ->orderBy('priority')
            ->orderBy('last_used_at')
            ->orderBy('id')
            ->first();

        if (!$smtp) {
            $this->warn("No active SMTP for account #{$accountId} — skipping enrollment #{$enrollment->id}");
            return;
        }

        // Check daily limit
        $today = Carbon::today()->toDateString();
        if (!is_null($smtp->daily_limit)) {
            $sentToday = SmtpServerUsage::where('smtp_server_id', $smtp->id)
                ->where('usage_date', $today)
                ->value('sent_count') ?? 0;
            if ($sentToday >= (int) $smtp->daily_limit) {
                $this->warn("SMTP #{$smtp->id} daily limit reached — skipping.");
                return;
            }
        }

        // Create an email_queue row for tracking
        $queueRow = EmailQueue::create([
            'account_id'  => $accountId,
            'contact_id'  => $contact->id,
            'email'       => $contact->email,
            'type'        => 'drip',
            'subject'     => $step->subject,
            'body'        => $step->body,
            'body_snapshot' => $step->body,
            'status'      => 'pending',
            'attempts'    => 0,
        ]);

        // Configure mailer dynamically
        config([
            'mail.default'                      => 'smtp',
            'mail.mailers.smtp.transport'        => 'smtp',
            'mail.mailers.smtp.host'             => $smtp->host,
            'mail.mailers.smtp.port'             => $smtp->port,
            'mail.mailers.smtp.encryption'       => $smtp->encryption === 'none' ? null : $smtp->encryption,
            'mail.mailers.smtp.username'         => $smtp->username,
            'mail.mailers.smtp.password'         => $smtp->password,
            'mail.mailers.smtp.timeout'          => 8,
            'mail.from.address'                  => $smtp->from_email,
            'mail.from.name'                     => $smtp->from_name,
        ]);

        try {
            Mail::to($contact->email)->send(new DripMail($step, $contact, $queueRow->id));

            $queueRow->update(['status' => 'sent', 'sent_at' => now()]);
            $smtp->update(['last_used_at' => now()]);

            // Track SMTP usage
            $usage = SmtpServerUsage::firstOrCreate(
                ['smtp_server_id' => $smtp->id, 'usage_date' => $today],
                ['account_id' => $accountId, 'sent_count' => 0, 'fail_count' => 0]
            );
            $usage->increment('sent_count');

            // Advance enrollment to next step
            $nextStep = $steps->get($stepIndex + 1);

            if ($nextStep) {
                $enrollment->update([
                    'current_step' => $enrollment->current_step + 1,
                    'next_send_at' => now()->addDays($nextStep->delay_days),
                ]);
                $this->info("Sent step {$enrollment->current_step} to {$contact->email} — next in {$nextStep->delay_days} day(s).");
            } else {
                $enrollment->update(['status' => 'completed', 'next_send_at' => null]);
                $this->info("Sent final step to {$contact->email} — enrollment completed.");
            }
        } catch (\Throwable $e) {
            $queueRow->update(['status' => 'failed', 'attempts' => 1, 'last_error' => $e->getMessage()]);

            $smtp->update(['last_used_at' => now()]);
            $usage = SmtpServerUsage::firstOrCreate(
                ['smtp_server_id' => $smtp->id, 'usage_date' => $today],
                ['account_id' => $accountId, 'sent_count' => 0, 'fail_count' => 0]
            );
            $usage->increment('fail_count');

            $this->error("Failed to send drip step to {$contact->email}: {$e->getMessage()}");
        }
    }
}
