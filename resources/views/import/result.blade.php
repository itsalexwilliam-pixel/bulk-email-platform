@extends('layouts.app')

@section('page_title', 'Import Summary')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Import Summary</h2>
        <p class="text-sm text-slate-500 mt-1">CSV import completed. Review the processed results below.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Rows</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $total }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-950/30 p-5 shadow-sm">
            <p class="text-sm text-emerald-700 dark:text-emerald-300">Imported</p>
            <p class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-300">{{ $imported }}</p>
        </div>
        <div class="rounded-2xl border border-rose-200 bg-rose-50 dark:bg-rose-950/30 p-5 shadow-sm">
            <p class="text-sm text-rose-700 dark:text-rose-300">Skipped</p>
            <p class="mt-2 text-3xl font-bold text-rose-700 dark:text-rose-300">{{ $skipped }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('contacts.index') }}"
               class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                Go to Contacts
            </a>
            <a href="{{ route('import.index') }}"
               class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                Import Another File
            </a>
        </div>
    </div>
</div>
@endsection
