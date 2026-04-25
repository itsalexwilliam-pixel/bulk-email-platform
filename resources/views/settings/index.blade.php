@extends('layouts.app')

@section('page_title', 'Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-1">Application Settings</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Configure default sending and app behavior.</p>

        <form method="POST" action="{{ route('settings.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-2">App Name</label>
                <input type="text" name="app_name" value="{{ old('app_name', $settings->app_name) }}"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('app_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-2">Default From Name</label>
                    <input type="text" name="default_from_name" value="{{ old('default_from_name', $settings->default_from_name) }}"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('default_from_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-2">Default From Email</label>
                    <input type="email" name="default_from_email" value="{{ old('default_from_email', $settings->default_from_email) }}"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('default_from_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-2">Mail Rate Limit (per minute)</label>
                    <input type="number" min="1" max="1000" name="mail_rate_per_minute" value="{{ old('mail_rate_per_minute', $settings->mail_rate_per_minute) }}"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('mail_rate_per_minute')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-2">Timezone</label>
                    <input type="text" name="timezone" value="{{ old('timezone', $settings->timezone) }}"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('timezone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit" class="inline-flex items-center rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 text-sm font-medium">
                    Save Settings
                </button>
                <a href="{{ route('profile.edit') }}" class="inline-flex items-center rounded-lg border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                    Account Settings
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
