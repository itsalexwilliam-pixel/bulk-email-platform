<?php

namespace App\Http\Controllers;

use App\Models\SmtpServer;
use App\Models\SmtpServerUsage;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SMTPController extends Controller
{
    public function index(Request $request)
    {
        $accountId = $this->getAccountId($request);

        $servers = SmtpServer::forAccount($accountId)
            ->latest()
            ->paginate(10);

        return view('smtp.index', compact('servers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $accountId = $this->getAccountId($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('smtp_servers', 'name')->where('account_id', $accountId)],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'encryption' => ['required', 'in:tls,ssl,none'],
            'from_email' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'daily_limit' => ['nullable', 'integer', 'min:1'],
            'priority' => ['nullable', 'integer', 'min:1'],
        ]);

        $duplicate = SmtpServer::forAccount($accountId)
            ->where('host', $data['host'])
            ->where('username', $data['username'])
            ->exists();

        if ($duplicate) {
            return back()->withErrors([
                'host' => 'An SMTP entry with the same host and username already exists for this account.',
            ])->withInput();
        }

        $data['account_id'] = $accountId;
        $data['is_active'] = true;

        SmtpServer::create($data);

        return redirect()->route('smtp.index')->with('success', 'SMTP server created.');
    }

    public function edit(Request $request, SmtpServer $smtp)
    {
        $this->guardAccountAccess($request, $smtp);

        return view('smtp.edit', ['server' => $smtp]);
    }

    public function update(Request $request, SmtpServer $smtp): RedirectResponse
    {
        $this->guardAccountAccess($request, $smtp);

        $accountId = $this->getAccountId($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('smtp_servers', 'name')->where('account_id', $accountId)->ignore($smtp->id)],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['required', 'in:tls,ssl,none'],
            'from_email' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'daily_limit' => ['nullable', 'integer', 'min:1'],
            'priority' => ['nullable', 'integer', 'min:1'],
        ]);

        $duplicate = SmtpServer::forAccount($accountId)
            ->where('host', $data['host'])
            ->where('username', $data['username'])
            ->where('id', '!=', $smtp->id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors([
                'host' => 'An SMTP entry with the same host and username already exists for this account.',
            ])->withInput();
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $smtp->update($data);

        return redirect()->route('smtp.index')->with('success', 'SMTP server updated.');
    }

    public function toggle(Request $request, SmtpServer $smtp): RedirectResponse
    {
        $this->guardAccountAccess($request, $smtp);

        $smtp->update(['is_active' => ! $smtp->is_active]);

        return redirect()->route('smtp.index')->with('success', 'SMTP status updated.');
    }

    public function destroy(Request $request, SmtpServer $smtp): RedirectResponse
    {
        $this->guardAccountAccess($request, $smtp);

        $smtp->delete();

        return redirect()->route('smtp.index')->with('success', 'SMTP server deleted.');
    }

    public function testConnection(Request $request, SmtpServer $smtp): RedirectResponse
    {
        $this->guardAccountAccess($request, $smtp);

        try {
            $this->applySmtpConfig($smtp);

            Mail::raw('SMTP connection test successful.', function ($message) use ($smtp) {
                $message->to($smtp->from_email)
                    ->subject('SMTP Connection Test');
            });

            return back()->with('success', "SMTP test successful for {$smtp->name}.");
        } catch (\Throwable $e) {
            Log::warning('SMTP test failed', [
                'smtp_id' => $smtp->id,
                'account_id' => $smtp->account_id,
                'error_type' => class_basename($e),
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'smtp_test' => "SMTP test failed for {$smtp->name}: " . $e->getMessage(),
            ]);
        }
    }

    public function sendTestEmail(Request $request, SmtpServer $smtp): RedirectResponse
    {
        $this->guardAccountAccess($request, $smtp);

        $data = $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        try {
            $this->applySmtpConfig($smtp);

            Mail::raw("This is a test email from SMTP server: {$smtp->name}", function ($message) use ($smtp, $data) {
                $message->to($data['test_email'])
                    ->subject('Test Email - SMTP Configuration')
                    ->from($smtp->from_email, $smtp->from_name);
            });

            return back()->with('success', "Test email sent successfully via {$smtp->name}.");
        } catch (\Throwable $e) {
            Log::warning('SMTP send test email failed', [
                'smtp_id' => $smtp->id,
                'account_id' => $smtp->account_id,
                'error_type' => class_basename($e),
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'smtp_test_email' => "Failed to send test email via {$smtp->name}: " . $e->getMessage(),
            ]);
        }
    }

    public function bulkUpload(Request $request): RedirectResponse
    {
        $accountId = $this->getAccountId($request);

        $request->validate([
            'smtp_csv' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $path = $request->file('smtp_csv')->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            return back()->withErrors(['smtp_csv' => 'Unable to read uploaded CSV file.']);
        }

        $headers = fgetcsv($handle);
        if (! $headers) {
            fclose($handle);
            return back()->withErrors(['smtp_csv' => 'CSV file is empty.']);
        }

        $requiredHeaders = ['label', 'host', 'port', 'username', 'password', 'encryption', 'from_email', 'from_name'];
        $normalizedHeaders = array_map(fn ($h) => strtolower(trim((string) $h)), $headers);

        foreach ($requiredHeaders as $requiredHeader) {
            if (! in_array($requiredHeader, $normalizedHeaders, true)) {
                fclose($handle);
                return back()->withErrors(['smtp_csv' => "Missing required CSV header: {$requiredHeader}"]);
            }
        }

        $headerMap = array_flip($normalizedHeaders);

        $successCount = 0;
        $failedRows = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            $payload = [
                'name' => trim((string) ($row[$headerMap['label']] ?? '')),
                'host' => trim((string) ($row[$headerMap['host']] ?? '')),
                'port' => (int) trim((string) ($row[$headerMap['port']] ?? '0')),
                'username' => trim((string) ($row[$headerMap['username']] ?? '')),
                'password' => trim((string) ($row[$headerMap['password']] ?? '')),
                'encryption' => trim((string) ($row[$headerMap['encryption']] ?? '')),
                'from_email' => trim((string) ($row[$headerMap['from_email']] ?? '')),
                'from_name' => trim((string) ($row[$headerMap['from_name']] ?? '')),
                'daily_limit' => null,
                'priority' => null,
            ];

            $validator = Validator::make($payload, [
                'name' => ['required', 'string', 'max:255', Rule::unique('smtp_servers', 'name')->where('account_id', $accountId)],
                'host' => ['required', 'string', 'max:255'],
                'port' => ['required', 'integer', 'min:1', 'max:65535'],
                'username' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'max:255'],
                'encryption' => ['required', 'in:tls,ssl,none'],
                'from_email' => ['required', 'email', 'max:255'],
                'from_name' => ['required', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                $failedRows[] = [
                    'row' => $rowNumber,
                    'reason' => $validator->errors()->first(),
                ];
                continue;
            }

            $duplicate = SmtpServer::forAccount($accountId)
                ->where('host', $payload['host'])
                ->where('username', $payload['username'])
                ->exists();

            if ($duplicate) {
                $failedRows[] = [
                    'row' => $rowNumber,
                    'reason' => 'Duplicate host+username for this account.',
                ];
                continue;
            }

            SmtpServer::create([
                'account_id' => $accountId,
                'name' => $payload['name'],
                'host' => $payload['host'],
                'port' => $payload['port'],
                'username' => $payload['username'],
                'password' => $payload['password'],
                'encryption' => $payload['encryption'],
                'from_email' => $payload['from_email'],
                'from_name' => $payload['from_name'],
                'is_active' => true,
                'daily_limit' => null,
                'priority' => null,
            ]);

            $successCount++;
        }

        fclose($handle);

        return redirect()->route('smtp.index')->with([
            'smtp_bulk_success_count' => $successCount,
            'smtp_bulk_failed_rows' => $failedRows,
            'success' => "Bulk upload completed. Added {$successCount} SMTP server(s).",
        ]);
    }

    public function health(Request $request)
    {
        $accountId = $this->getAccountId($request);

        $servers = SmtpServer::forAccount($accountId)->orderBy('priority')->orderBy('id')->get();

        $today = Carbon::today()->toDateString();
        $last7 = Carbon::today()->subDays(6)->toDateString();

        $usageBySmtp = SmtpServerUsage::whereIn('smtp_server_id', $servers->pluck('id'))
            ->where('usage_date', '>=', $last7)
            ->get()
            ->groupBy('smtp_server_id');

        $stats = $servers->map(function (SmtpServer $smtp) use ($usageBySmtp, $today) {
            $usages = $usageBySmtp->get($smtp->id, collect());

            $todayUsage = $usages->firstWhere('usage_date', $today);
            $sentToday  = (int) ($todayUsage->sent_count ?? 0);
            $failToday  = (int) ($todayUsage->fail_count ?? 0);
            $totalSent7 = $usages->sum('sent_count');
            $totalFail7 = $usages->sum('fail_count');

            $successRate = ($sentToday + $failToday) > 0
                ? round(($sentToday / ($sentToday + $failToday)) * 100, 1)
                : null;

            return [
                'id'           => $smtp->id,
                'name'         => $smtp->name,
                'host'         => $smtp->host,
                'is_active'    => $smtp->is_active,
                'daily_limit'  => $smtp->daily_limit,
                'priority'     => $smtp->priority,
                'sent_today'   => $sentToday,
                'fail_today'   => $failToday,
                'total_sent_7' => $totalSent7,
                'total_fail_7' => $totalFail7,
                'success_rate' => $successRate,
                'last_used_at' => $smtp->last_used_at?->diffForHumans() ?? 'Never',
                'daily_usage_pct' => $smtp->daily_limit
                    ? min(100, round(($sentToday / $smtp->daily_limit) * 100))
                    : null,
            ];
        });

        return view('smtp.health', compact('stats'));
    }

    private function guardAccountAccess(Request $request, SmtpServer $smtp): void
    {
        if ((int) $smtp->account_id !== $this->getAccountId($request)) {
            abort(403, 'Forbidden');
        }
    }

    private function applySmtpConfig(SmtpServer $smtp): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $smtp->host,
            'mail.mailers.smtp.port' => $smtp->port,
            'mail.mailers.smtp.username' => $smtp->username,
            'mail.mailers.smtp.password' => $smtp->password,
            'mail.mailers.smtp.encryption' => $smtp->encryption === 'none' ? null : $smtp->encryption,
            'mail.mailers.smtp.timeout' => 8,
            'mail.from.address' => $smtp->from_email,
            'mail.from.name' => $smtp->from_name,
        ]);
    }
}
