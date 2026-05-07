<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = AppSetting::first();

        if (!$settings) {
            $settings = AppSetting::create([
                'app_name' => config('app.name', 'Bulk Mailer'),
                'default_from_name' => config('mail.from.name'),
                'default_from_email' => config('mail.from.address'),
                'mail_rate_per_minute' => 60,
                'timezone' => config('app.timezone', 'UTC'),
            ]);
        }

        return view('settings.index', compact('settings'));
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
}
