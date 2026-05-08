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

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Configured Servers</h3>
        </div>
        @if($servers->isEmpty())
            <div class="p-10 text-center text-slate-500 dark:text-slate-400">
                No SMTP servers configured.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        <tr class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                            <th class="px-5 py-3">Name</th>
                            <th class="px-5 py-3">Host / Port</th>
                            <th class="px-5 py-3">Encryption</th>
                            <th class="px-5 py-3">From</th>
                            <th class="px-5 py-3">Limit / Priority</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($servers as $server)
                            <tr class="border-t border-slate-100 dark:border-slate-800 hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">

                                <td class="px-5 py-3">
                                    <span class="font-medium text-slate-900 dark:text-white">{{ $server->name }}</span>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $server->username }}</p>
                                </td>

                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                    {{ $server->host }}<span class="text-slate-400">:{{ $server->port }}</span>
                                </td>

                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 uppercase">
                                        {{ $server->encryption }}
                                    </span>
                                </td>

                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300 max-w-xs">
                                    <span class="block truncate" title="{{ $server->from_name }} <{{ $server->from_email }}>">
                                        {{ $server->from_name }} &lt;{{ $server->from_email }}&gt;
                                    </span>
                                </td>

                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400 text-xs whitespace-nowrap">
                                    {{ $server->daily_limit ?? '∞' }} / {{ $server->priority ?? 0 }}
                                </td>

                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full
                                        {{ $server->is_active
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                                            : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $server->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $server->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        <a href="{{ route('smtp.edit', $server) }}"
                                           class="px-2.5 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 text-xs font-medium hover:bg-indigo-100 transition border border-indigo-200 dark:border-indigo-500/30">
                                            Edit
                                        </a>

                                        <form action="{{ route('smtp.test', $server) }}" method="POST">
                                            @csrf
                                            <button class="px-2.5 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 text-xs font-medium hover:bg-blue-100 transition border border-blue-200 dark:border-blue-500/30">
                                                Test
                                            </button>
                                        </form>

                                        {{-- Send test email (toggle inline) --}}
                                        <button type="button" onclick="toggleTestMailRow('{{ $server->id }}')"
                                                class="px-2.5 py-1.5 rounded-lg bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 text-xs font-medium hover:bg-cyan-100 transition border border-cyan-200 dark:border-cyan-500/30">
                                            Send Mail
                                        </button>

                                        <form action="{{ route('smtp.toggle', $server) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button class="px-2.5 py-1.5 rounded-lg text-xs font-medium border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition whitespace-nowrap">
                                                {{ $server->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('smtp.destroy', $server) }}" method="POST"
                                              onsubmit="return confirm('Delete this SMTP server?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-2.5 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-300 text-xs font-medium hover:bg-rose-100 transition border border-rose-200 dark:border-rose-500/30">
                                                Delete
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Inline test email row (hidden by default) --}}
                                    <div id="test-mail-row-{{ $server->id }}" class="hidden mt-2">
                                        <form action="{{ route('smtp.send-test-email', $server) }}" method="POST"
                                              class="flex items-center gap-2">
                                            @csrf
                                            <input type="email" name="test_email" required placeholder="test@example.com"
                                                   class="flex-1 min-w-0 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-2.5 py-1.5 text-xs">
                                            <button class="shrink-0 px-2.5 py-1.5 rounded-lg bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 text-xs font-medium hover:bg-cyan-100 transition border border-cyan-200 dark:border-cyan-500/30">
                                                Send
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div>{{ $servers->links() }}</div>
</div>

<script>
function toggleTestMailRow(id) {
    const row = document.getElementById('test-mail-row-' + id);
    if (row) row.classList.toggle('hidden');
}

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
