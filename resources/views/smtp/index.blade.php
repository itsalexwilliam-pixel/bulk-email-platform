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

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Add SMTP Server</h2>
        </div>

        <form method="POST" action="{{ route('smtp.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Host</label>
                    <input type="text" name="host" value="{{ old('host') }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Port</label>
                    <input type="number" name="port" value="{{ old('port', 587) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Encryption</label>
                    <select name="encryption" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                        <option value="tls" @selected(old('encryption') === 'tls')>TLS</option>
                        <option value="ssl" @selected(old('encryption') === 'ssl')>SSL</option>
                        <option value="none" @selected(old('encryption') === 'none')>None</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Password</label>
                    <input type="password" name="password" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">From Name</label>
                    <input type="text" name="from_name" value="{{ old('from_name') }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">From Email</label>
                    <input type="email" name="from_email" value="{{ old('from_email') }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                </div>
            </div>

            <button class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                Save SMTP Server
            </button>
        </form>
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
                        <p><span class="text-slate-400">From:</span> {{ $server->from_name }} <{{ $server->from_email }}></p>
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
@endsection
