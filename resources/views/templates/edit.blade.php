@extends('layouts.app')

@section('page_title', 'Edit Template')

@section('content')
<div class="space-y-6">

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('templates.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Edit Template</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $template->name }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('templates.update', $template) }}" class="space-y-5">
        @csrf @method('PUT')
        @include('templates._form', ['template' => $template])
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm flex gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                Update Template
            </button>
            <a href="{{ route('templates.index') }}"
               class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                Cancel
            </a>
        </div>
    </form>

</div>
@endsection
