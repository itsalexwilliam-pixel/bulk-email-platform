<?php

namespace App\Http\Controllers;

use App\Models\SmtpServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SMTPController extends Controller
{
    public function index()
    {
        $servers = SmtpServer::latest()->paginate(10);
        return view('smtp.index', compact('servers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'encryption' => 'required|in:tls,ssl,none',
            'from_email' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
        ]);

        SmtpServer::create($data);

        return redirect()->route('smtp.index')->with('success', 'SMTP server created.');
    }

    public function edit(SmtpServer $smtp)
    {
        return view('smtp.edit', ['server' => $smtp]);
    }

    public function update(Request $request, SmtpServer $smtp)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
            'encryption' => 'required|in:tls,ssl,none',
            'from_email' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $smtp->update($data);

        return redirect()->route('smtp.index')->with('success', 'SMTP server updated.');
    }

    public function toggle(SmtpServer $smtp)
    {
        $smtp->update(['is_active' => !$smtp->is_active]);

        return redirect()->route('smtp.index')->with('success', 'SMTP status updated.');
    }

    public function destroy(SmtpServer $smtp)
    {
        $smtp->delete();

        return redirect()->route('smtp.index')->with('success', 'SMTP server deleted.');
    }

    public function testConnection(SmtpServer $smtp)
    {
        try {
            $this->applySmtpConfig($smtp);

            Mail::raw('SMTP connection test successful.', function ($message) use ($smtp) {
                $message->to($smtp->from_email)
                    ->subject('SMTP Connection Test');
            });

            return back()->with('success', "SMTP test successful for {$smtp->name}.");
        } catch (\Throwable $e) {
            return back()->withErrors(['smtp_test' => "SMTP test failed for {$smtp->name}: {$e->getMessage()}"]);
        }
    }

    public function sendTestEmail(Request $request, SmtpServer $smtp)
    {
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
            return back()->withErrors(['smtp_test_email' => "Failed to send test email via {$smtp->name}: {$e->getMessage()}"]);
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
            'mail.from.address' => $smtp->from_email,
            'mail.from.name' => $smtp->from_name,
        ]);
    }
}
