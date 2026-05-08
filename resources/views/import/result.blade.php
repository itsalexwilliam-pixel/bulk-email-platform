@extends('layouts.app')

@section('page_title', 'Import Summary')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('import.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Import Summary</h2>
                <p class="text-sm text-slate-500 mt-0.5">CSV import completed. Review the results below.</p>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <p class="text-sm text-slate-500 dark:text-slate-400">Total Rows Processed</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $total }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/30 p-5 shadow-sm">
            <p class="text-sm text-emerald-700 dark:text-emerald-300">Imported</p>
            <p class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-300">{{ $imported }}</p>
        </div>
        <div class="rounded-2xl border {{ $skipped > 0 ? 'border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/30' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900' }} p-5 shadow-sm">
            <p class="text-sm {{ $skipped > 0 ? 'text-rose-700 dark:text-rose-300' : 'text-slate-500 dark:text-slate-400' }}">Skipped</p>
            <p class="mt-2 text-3xl font-bold {{ $skipped > 0 ? 'text-rose-700 dark:text-rose-300' : 'text-slate-900 dark:text-white' }}">{{ $skipped }}</p>
        </div>
    </div>

    @if($skipped === 0)
        <div class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/30 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
            All {{ $imported }} rows were imported successfully — no errors found.
        </div>
    @endif

    {{-- Per-row failure table --}}
    @if(!empty($failedRows))
        <div class="rounded-2xl border border-rose-200 dark:border-rose-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-rose-100 dark:border-rose-900/50 bg-rose-50/70 dark:bg-rose-950/20">
                <div>
                    <h3 class="font-semibold text-rose-700 dark:text-rose-300">Skipped Rows — Validation Errors</h3>
                    <p class="text-xs text-rose-600/80 dark:text-rose-400 mt-0.5">{{ count($failedRows) }} row(s) were not imported. Fix the data in your CSV and re-import if needed.</p>
                </div>
                <span class="shrink-0 px-2.5 py-1 text-xs font-semibold rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300">
                    {{ count($failedRows) }} skipped
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr class="text-left text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wide">
                            <th class="py-2.5 px-4 w-16">Row #</th>
                            <th class="py-2.5 px-4">Name</th>
                            <th class="py-2.5 px-4">Email</th>
                            <th class="py-2.5 px-4">Reason(s) Skipped</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($failedRows as $failed)
                            <tr class="border-t border-slate-100 dark:border-slate-800 hover:bg-rose-50/40 dark:hover:bg-rose-950/20 transition">
                                <td class="py-3 px-4 text-slate-400 dark:text-slate-500 font-mono text-xs">{{ $failed['row'] }}</td>
                                <td class="py-3 px-4 text-slate-700 dark:text-slate-300">{{ $failed['name'] }}</td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400 font-mono text-xs">{{ $failed['email'] }}</td>
                                <td class="py-3 px-4">
                                    <ul class="space-y-0.5">
                                        @foreach($failed['reasons'] as $reason)
                                            <li class="flex items-start gap-1.5 text-rose-600 dark:text-rose-400 text-xs">
                                                <svg class="w-3.5 h-3.5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ $reason }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Actions --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm flex flex-wrap items-center gap-3">
        <a href="{{ route('contacts.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            View Contacts
        </a>
        <a href="{{ route('import.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import Another File
        </a>
    </div>

</div>
@endsection
