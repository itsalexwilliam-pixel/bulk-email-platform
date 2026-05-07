@extends('layouts.app')

@section('page_title', 'Edit SMTP Server')

@section('content')
<div class="max-w-6xl space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
            <p class="font-medium mb-1">Please fix the following:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Edit SMTP Server</h2>
        <p class="text-sm text-slate-500 mt-1">Update SMTP credentials and sender identity.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <form method="POST" action="{{ route('smtp.update', $server) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Provider</label>
                    <select name="provider_preset" data-provider-select data-target-prefix="edit" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                        <option value="custom" @selected(old('provider_preset') === 'custom')>Custom</option>
                        <option value="gmail" @selected(old('provider_preset') === 'gmail')>Gmail</option>
                        <option value="outlook" @selected(old('provider_preset') === 'outlook')>Outlook</option>
                        <option value="zoho" @selected(old('provider_preset') === 'zoho')>Zoho</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Name (Label)</label>
                    <input type="text" name="name" value="{{ old('name', $server->name) }}" required class="w-full rounded-xl border @error('name') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Host</label>
                    <input id="edit-host" type="text" name="host" value="{{ old('host', $server->host) }}" required class="w-full rounded-xl border @error('host') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('host')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Port</label>
                    <input id="edit-port" type="number" name="port" value="{{ old('port', $server->port) }}" required class="w-full rounded-xl border @error('port') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('port')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Encryption</label>
                    <select id="edit-encryption" name="encryption" required class="w-full rounded-xl border @error('encryption') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                        <option value="tls" @selected(old('encryption', $server->encryption) === 'tls')>TLS</option>
                        <option value="ssl" @selected(old('encryption', $server->encryption) === 'ssl')>SSL</option>
                        <option value="none" @selected(old('encryption', $server->encryption) === 'none')>None</option>
                    </select>
                    @error('encryption')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Username</label>
                    <input type="text" name="username" value="{{ old('username', $server->username) }}" required class="w-full rounded-xl border @error('username') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('username')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Current Password</label>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">******</p>
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">New Password (optional)</label>
                    <input type="password" name="password" class="w-full rounded-xl border @error('password') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    <p class="text-xs text-slate-500 mt-1">Leave blank to keep existing password.</p>
                    @error('password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">From Name</label>
                    <input type="text" name="from_name" value="{{ old('from_name', $server->from_name) }}" required class="w-full rounded-xl border @error('from_name') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('from_name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">From Email</label>
                    <input type="email" name="from_email" value="{{ old('from_email', $server->from_email) }}" required class="w-full rounded-xl border @error('from_email') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('from_email')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Daily Limit (optional)</label>
                    <input type="number" name="daily_limit" min="1" value="{{ old('daily_limit', $server->daily_limit) }}" class="w-full rounded-xl border @error('daily_limit') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('daily_limit')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Priority</label>
                    <input type="number" name="priority" min="0" value="{{ old('priority', $server->priority ?? 0) }}" class="w-full rounded-xl border @error('priority') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('priority')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                    Update SMTP Server
                </button>
                <a href="{{ route('smtp.index') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Back
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const presets = {
        gmail: { host: 'smtp.gmail.com', port: '587', encryption: 'tls' },
        outlook: { host: 'smtp.office365.com', port: '587', encryption: 'tls' },
        zoho: { host: 'smtp.zoho.com', port: '587', encryption: 'tls' }
    };

    function wireProviderSelect(selectEl) {
        const prefix = selectEl.dataset.targetPrefix;
        const hostEl = document.getElementById(prefix + '-host');
        const portEl = document.getElementById(prefix + '-port');
        const encryptionEl = document.getElementById(prefix + '-encryption');

        if (!hostEl || !portEl || !encryptionEl) return;

        let userEdited = {
            host: false,
            port: false,
            encryption: false
        };

        hostEl.addEventListener('input', () => userEdited.host = true);
        portEl.addEventListener('input', () => userEdited.port = true);
        encryptionEl.addEventListener('change', () => userEdited.encryption = true);

        selectEl.addEventListener('change', function () {
            const value = this.value;
            if (!presets[value]) return;

            if (!userEdited.host) hostEl.value = presets[value].host;
            if (!userEdited.port) portEl.value = presets[value].port;
            if (!userEdited.encryption) encryptionEl.value = presets[value].encryption;
        });
    }

    document.querySelectorAll('[data-provider-select]').forEach(wireProviderSelect);
});
</script>
@endsection
