@extends('layouts.app')

@section('page_title', 'SMTP / Sending')

@section('content')
@php
    $totalServers = $servers->total();
    $activeServers = $servers->where('is_active', 1)->count();
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-saas-stat-card title="Total Servers" :value="$totalServers" />
        <x-saas-stat-card title="Active" :value="$activeServers" />
        <x-saas-stat-card title="Inactive" :value="max($totalServers - $activeServers, 0)" />
    </div>

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
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Add SMTP Server</h2>
        </div>

        <form method="POST" action="{{ route('smtp.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Provider</label>
                    <select name="provider_preset" data-provider-select data-target-prefix="create" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                        <option value="custom" @selected(old('provider_preset') === 'custom')>Custom</option>
                        <option value="gmail" @selected(old('provider_preset') === 'gmail')>Gmail</option>
                        <option value="outlook" @selected(old('provider_preset') === 'outlook')>Outlook</option>
                        <option value="zoho" @selected(old('provider_preset') === 'zoho')>Zoho</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Name (Label)</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border @error('name') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Host</label>
                    <input id="create-host" type="text" name="host" value="{{ old('host') }}" required class="w-full rounded-xl border @error('host') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('host')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Port</label>
                    <input id="create-port" type="number" name="port" value="{{ old('port', 587) }}" required class="w-full rounded-xl border @error('port') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('port')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Encryption</label>
                    <select id="create-encryption" name="encryption" required class="w-full rounded-xl border @error('encryption') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                        <option value="tls" @selected(old('encryption') === 'tls')>TLS</option>
                        <option value="ssl" @selected(old('encryption') === 'ssl')>SSL</option>
                        <option value="none" @selected(old('encryption') === 'none')>None</option>
                    </select>
                    @error('encryption')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" required class="w-full rounded-xl border @error('username') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('username')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Password</label>
                    <input type="password" name="password" required class="w-full rounded-xl border @error('password') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">From Name</label>
                    <input type="text" name="from_name" value="{{ old('from_name') }}" required class="w-full rounded-xl border @error('from_name') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('from_name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">From Email</label>
                    <input type="email" name="from_email" value="{{ old('from_email') }}" required class="w-full rounded-xl border @error('from_email') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('from_email')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Daily Limit (optional)</label>
                    <input type="number" name="daily_limit" min="1" value="{{ old('daily_limit') }}" class="w-full rounded-xl border @error('daily_limit') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('daily_limit')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Priority</label>
                    <input type="number" name="priority" min="0" value="{{ old('priority', 0) }}" class="w-full rounded-xl border @error('priority') border-rose-400 @else border-slate-300 dark:border-slate-700 @enderror bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                    @error('priority')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <button class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                Save SMTP Server
            </button>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-3">Bulk Upload SMTP (.csv only)</h3>
        <form method="POST" action="{{ route('smtp.bulk-upload') }}" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-3 md:items-center">
            @csrf
            <input type="file" name="smtp_csv" accept=".csv,text/csv" required class="block w-full md:w-auto text-sm text-slate-700 dark:text-slate-200">
            <button class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                Upload CSV
            </button>
        </form>
        <p class="text-xs text-slate-500 mt-2">
            Required headers: label,host,port,username,password,encryption,from_email,from_name
        </p>

        @if(session()->has('smtp_bulk_success_count') || session()->has('smtp_bulk_failed_rows'))
            <div class="mt-4 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">
                    Success count: {{ session('smtp_bulk_success_count', 0) }}
                </p>

                @php($failedRows = session('smtp_bulk_failed_rows', []))
                @if(!empty($failedRows))
                    <div class="mt-3">
                        <p class="text-sm font-medium text-rose-700 dark:text-rose-300 mb-1">Failed rows</p>
                        <ul class="space-y-1 text-xs text-rose-700 dark:text-rose-300">
                            @foreach($failedRows as $failed)
                                <li>Row {{ $failed['row'] ?? '?' }}: {{ $failed['reason'] ?? 'Validation failed' }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div>
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-3">Configured Servers</h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @forelse($servers as $server)
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-base font-semibold text-slate-900 dark:text-white">{{ $server->name }}</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $server->host }}:{{ $server->port }} • {{ strtoupper($server->encryption) }}</p>
                        </div>
                        <span class="inline-flex items-center gap-2 text-xs font-medium px-2.5 py-1 rounded-full {{ $server->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                            <span class="w-2 h-2 rounded-full {{ $server->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                            {{ $server->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="mt-3 text-sm text-slate-600 dark:text-slate-300 space-y-1">
                        <p><span class="text-slate-400">Username:</span> {{ $server->username }}</p>
                        <p><span class="text-slate-400">Password:</span> ******</p>
                        <p><span class="text-slate-400">From:</span> {{ $server->from_name }} <{{ $server->from_email }}></p>
                        <p><span class="text-slate-400">Daily Limit:</span> {{ $server->daily_limit ?? '—' }}</p>
                        <p><span class="text-slate-400">Priority:</span> {{ $server->priority ?? 0 }}</p>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <a href="{{ route('smtp.edit', $server) }}" class="px-3 py-2 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-medium hover:bg-indigo-200 transition">
                            Edit
                        </a>

                        <form action="{{ route('smtp.test', $server) }}" method="POST">
                            @csrf
                            <button class="px-3 py-2 rounded-lg bg-blue-100 text-blue-700 text-xs font-medium hover:bg-blue-200 transition">
                                Test SMTP
                            </button>
                        </form>

                        <form action="{{ route('smtp.send-test-email', $server) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            <input type="email" name="test_email" required placeholder="test@example.com" class="w-44 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-2.5 py-2 text-xs">
                            <button class="px-3 py-2 rounded-lg bg-cyan-100 text-cyan-700 text-xs font-medium hover:bg-cyan-200 transition">
                                Send Test Mail
                            </button>
                        </form>

                        <form action="{{ route('smtp.toggle', $server) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                <span class="relative inline-flex h-4 w-8 items-center rounded-full {{ $server->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}">
                                    <span class="inline-block h-3 w-3 transform rounded-full bg-white transition {{ $server->is_active ? 'translate-x-4' : 'translate-x-1' }}"></span>
                                </span>
                                {{ $server->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        <form action="{{ route('smtp.destroy', $server) }}" method="POST" onsubmit="return confirm('Delete this SMTP server?')">
                            @csrf
                            @method('DELETE')
                            <button class="px-3 py-2 rounded-lg bg-rose-100 text-rose-700 text-xs font-medium hover:bg-rose-200 transition">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-10 text-center text-slate-500">
                    No SMTP servers configured.
                </div>
            @endforelse
        </div>
    </div>

    <div>{{ $servers->links() }}</div>
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
