<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = AppSetting::first();

        if (!$settings) {
            $settings = AppSetting::create([
                'app_name' => config('app.name', 'Novelio Technologies'),
                'default_from_name' => config('mail.from.name'),
                'default_from_email' => config('mail.from.address'),
                'mail_rate_per_minute' => 60,
                'timezone' => config('app.timezone', 'UTC'),
            ]);
        }

        $accountId = $this->currentAccountId();
        $account = Account::findOrFail($accountId);

        return view('settings.index', compact('settings', 'account'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'default_from_name' => ['nullable', 'string', 'max:255'],
            'default_from_email' => ['nullable', 'email', 'max:255'],
            'mail_rate_per_minute' => ['required', 'integer', 'min:1', 'max:1000'],
            'timezone' => ['required', 'string', 'max:100'],
        ]);

        $settings = AppSetting::first();
        if (!$settings) {
            $settings = new AppSetting();
        }

        $settings->fill($validated);
        $settings->save();

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }

    public function updateWebhook(Request $request)
    {
        $request->validate([
            'webhook_url' => ['nullable', 'url', 'max:500'],
        ]);

        $accountId = $this->currentAccountId();
        $account = Account::findOrFail($accountId);
        $account->webhook_url = $request->input('webhook_url') ?: null;
        $account->save();

        return redirect()->route('settings.index')->with('success', 'Webhook URL saved.');
    }

    public function updateBranding(Request $request)
    {
        $request->validate([
            'unsubscribe_logo_url' => ['nullable', 'url', 'max:500'],
            'unsubscribe_message'  => ['nullable', 'string', 'max:1000'],
        ]);

        $settings = AppSetting::first();
        if (!$settings) {
            $settings = new AppSetting();
        }

        $settings->unsubscribe_logo_url = $request->input('unsubscribe_logo_url') ?: null;
        $settings->unsubscribe_message  = $request->input('unsubscribe_message') ?: null;
        $settings->save();

        return redirect()->route('settings.index')->with('success', 'Unsubscribe page branding saved.');
    }
}
