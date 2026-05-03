<?php

namespace App\Http\Controllers;

use App\Mail\SingleEmailMail;
use App\Models\EmailQueue;
use App\Models\SmtpServer;
use App\Models\SmtpServerUsage;
use App\Support\EmailHtmlPreprocessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SingleEmailController extends Controller
{
    public function create(Request $request)
    {
        $accountId = $this->getAccountId($request);

        $smtpServers = SmtpServer::forAccount($accountId)
            ->active()
            ->orderBy('priority')
            ->orderBy('last_used_at')
            ->orderBy('id')
            ->get();

        return view('single-email.create', compact('smtpServers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $accountId = $this->getAccountId($request);

        $data = $request->validate([
            'to' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'smtp_server_id' => ['required', 'integer', 'exists:smtp_servers,id'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png,gif,webp', 'max:10240'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
        ]);

        $smtp = SmtpServer::findOrFail($data['smtp_server_id']);

        if ((int) $smtp->account_id !== $accountId || ! $smtp->is_active) {
            abort(403, 'Forbidden');
        }

        $storedAttachments = [];
        $totalSize = 0;

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $totalSize += (int) $file->getSize();
            }

            if ($totalSize > 10 * 1024 * 1024) {
                return back()->withErrors([
                    'attachments' => 'Total attachment size must not exceed 10MB.',
                ])->withInput();
            }

            foreach ($request->file('attachments') as $file) {
                $path = $file->store('temp/single_emails', 'local');
                $storedAttachments[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                ];
            }
        }

        $rawMessage = $data['message'];
        $hasTemplateHtml = preg_match('/<(html|head|body|table|style|div)\b/i', $rawMessage) === 1;
        $processedMessage = $hasTemplateHtml
            ? $rawMessage
            : EmailHtmlPreprocessor::preprocess($rawMessage);

        $queueItem = EmailQueue::create([
            'campaign_id' => null,
            'contact_id' => null,
            'smtp_server_id' => $smtp->id,
            'email' => $data['to'],
            'type' => 'single',
            'subject' => $data['subject'],
            'body' => $processedMessage,
            'from_email' => $data['from_email'] ?? null,
            'from_name' => $data['from_name'] ?? null,
            'attachments' => $storedAttachments,
            'utm_source' => $data['utm_source'] ?? null,
            'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'utm_term' => $data['utm_term'] ?? null,
            'utm_content' => $data['utm_content'] ?? null,
            'status' => 'pending',
            'attempts' => 0,
            'last_error' => null,
            'sent_at' => null,
        ]);

        try {
            $this->applySmtpConfig($smtp, $data['from_email'] ?? null, $data['from_name'] ?? null);

            Log::info('SingleEmail HTML Preview', [
                'has_template_html' => $hasTemplateHtml,
                'sample' => substr($processedMessage, 0, 500),
            ]);

            Mail::to($data['to'])->send(
                new SingleEmailMail(
                    queueItem: $queueItem,
                    subjectLine: $data['subject'],
                    htmlBody: $processedMessage,
                    attachmentsMeta: $storedAttachments
                )
            );

            $queueItem->update([
                'status' => 'sent',
                'sent_at' => now(),
                'last_error' => null,
            ]);

            $smtp->update(['last_used_at' => now()]);
            $this->incrementSmtpUsage($smtp->id, $accountId, true);

            return redirect()->route('single-email.create')->with('success', 'Single email sent successfully.');
        } catch (\Throwable $e) {
            $queueItem->update([
                'status' => 'failed',
                'attempts' => ((int) $queueItem->attempts) + 1,
                'last_error' => 'connection failed / timeout / authentication failed',
            ]);

            $smtp->update(['last_used_at' => now()]);
            $this->incrementSmtpUsage($smtp->id, $accountId, false);

            Log::warning('Single email send failed', [
                'queue_id' => $queueItem->id,
                'smtp_id' => $smtp->id,
                'account_id' => $accountId,
                'error_type' => class_basename($e),
                'error' => 'connection failed / timeout / authentication failed',
            ]);

            return back()->withErrors([
                'single_email' => 'Failed to send single email: connection failed / timeout / authentication failed.',
            ])->withInput();
        } finally {
            foreach ($storedAttachments as $attachment) {
                if (!empty($attachment['path']) && Storage::disk('local')->exists($attachment['path'])) {
                    Storage::disk('local')->delete($attachment['path']);
                }
            }
        }
    }

    private function getAccountId(Request $request): int
    {
        $accountId = (int) ($request->user()?->account_id ?? 0);
        abort_if($accountId <= 0, 403, 'Account context is missing.');

        return $accountId;
    }

    private function applySmtpConfig(SmtpServer $smtp, ?string $fromEmailOverride = null, ?string $fromNameOverride = null): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $smtp->host,
            'mail.mailers.smtp.port' => $smtp->port,
            'mail.mailers.smtp.username' => $smtp->username,
            'mail.mailers.smtp.password' => $smtp->password,
            'mail.mailers.smtp.encryption' => $smtp->encryption === 'none' ? null : $smtp->encryption,
            'mail.mailers.smtp.timeout' => 8,
            'mail.from.address' => $fromEmailOverride ?: $smtp->from_email,
            'mail.from.name' => $fromNameOverride ?: $smtp->from_name,
        ]);
    }

    private function incrementSmtpUsage(int $smtpServerId, int $accountId, bool $success): void
    {
        $date = now()->toDateString();
        $sentIncrement = $success ? 1 : 0;
        $failIncrement = $success ? 0 : 1;
        $now = now();

        DB::table('smtp_server_usages')->upsert(
            [[
                'smtp_server_id' => $smtpServerId,
                'account_id' => $accountId,
                'usage_date' => $date,
                'sent_count' => $sentIncrement,
                'fail_count' => $failIncrement,
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['smtp_server_id', 'account_id', 'usage_date'],
            [
                'sent_count' => DB::raw('sent_count + '.$sentIncrement),
                'fail_count' => DB::raw('fail_count + '.$failIncrement),
                'updated_at' => $now,
            ]
        );
    }
}
