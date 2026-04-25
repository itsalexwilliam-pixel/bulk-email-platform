@extends('layouts.app')

@section('page_title', 'Edit SMTP Server')

@section('content')
<div class="max-w-6xl space-y-6">
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
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Name</label>
                    <input type="text" name="name" value="{{ old('name', $server->name) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Host</label>
                    <input type="text" name="host" value="{{ old('host', $server->host) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Port</label>
                    <input type="number" name="port" value="{{ old('port', $server->port) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Encryption</label>
                    <select name="encryption" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                        <option value="tls" @selected(old('encryption', $server->encryption) === 'tls')>TLS</option>
                        <option value="ssl" @selected(old('encryption', $server->encryption) === 'ssl')>SSL</option>
                        <option value="none" @selected(old('encryption', $server->encryption) === 'none')>None</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Username</label>
                    <input type="text" name="username" value="{{ old('username', $server->username) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">Password (leave blank to keep unchanged)</label>
                    <input type="password" name="password" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">From Name</label>
                    <input type="text" name="from_name" value="{{ old('from_name', $server->from_name) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-slate-700 dark:text-slate-300">From Email</label>
                    <input type="email" name="from_email" value="{{ old('from_email', $server->from_email) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm">
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
@endsection
