<?php

namespace App\Http\Controllers;

use App\Models\SmtpServer;
use Illuminate\Http\Request;

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
}
