@extends('layouts.app')

@section('page_title', 'Email Templates')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Email Templates</h2>
                <p class="text-sm text-slate-500 mt-0.5">Save reusable email designs. Load them instantly when creating campaigns.</p>
            </div>
            <a href="{{ route('templates.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Template
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($templates->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 p-12 text-center">
            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-slate-500 dark:text-slate-400 font-medium mb-1">No templates yet</p>
            <p class="text-sm text-slate-400 mb-4">Create your first template to speed up campaign creation.</p>
            <a href="{{ route('templates.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                Create Template
            </a>
        </div>
    @else
        {{-- Category filter tabs --}}
        @if($categories->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                <button type="button" data-filter="all"
                        class="filter-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-600 text-white transition">
                    All
                </button>
                @foreach($categories as $cat)
                    <button type="button" data-filter="{{ $cat }}"
                            class="filter-btn px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        {{ $cat }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Table --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        <tr class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                            <th class="px-5 py-3">Name</th>
                            <th class="px-5 py-3">Subject</th>
                            <th class="px-5 py-3">Category</th>
                            <th class="px-5 py-3">Preview</th>
                            <th class="px-5 py-3">Saved</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="templateGrid">
                        @foreach($templates as $template)
                            <tr class="template-card border-t border-slate-100 dark:border-slate-800 hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition"
                                data-category="{{ $template->category }}">

                                <td class="px-5 py-3">
                                    <span class="font-medium text-slate-900 dark:text-white">{{ $template->name }}</span>
                                </td>

                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400 max-w-xs">
                                    <span class="block truncate" title="{{ $template->subject }}">
                                        {{ $template->subject ?: '—' }}
                                    </span>
                                </td>

                                <td class="px-5 py-3">
                                    @if($template->category)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300 font-medium">
                                            {{ $template->category }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <td class="px-5 py-3 text-slate-400 dark:text-slate-500 max-w-xs">
                                    <span class="block truncate text-xs" title="{{ strip_tags($template->body) }}">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($template->body), 80) }}
                                    </span>
                                </td>

                                <td class="px-5 py-3 text-slate-400 text-xs whitespace-nowrap">
                                    {{ optional($template->created_at)->diffForHumans() }}
                                </td>

                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('templates.edit', $template) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Edit
                                        </a>
                                        <a href="{{ route('campaigns.create') }}?template_id={{ $template->id }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 text-xs font-medium hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition border border-indigo-200 dark:border-indigo-500/30">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                            Use
                                        </a>
                                        <form method="POST" action="{{ route('templates.destroy', $template) }}"
                                              onsubmit="return confirm('Delete this template?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center justify-center p-1.5 rounded-lg border border-rose-200 dark:border-rose-800/50 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const filter = this.dataset.filter;
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.className = 'filter-btn px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition';
            });
            this.className = 'filter-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-600 text-white transition';

            document.querySelectorAll('.template-card').forEach(row => {
                row.style.display = (filter === 'all' || row.dataset.category === filter) ? '' : 'none';
            });
        });
    });
</script>
@endpush
