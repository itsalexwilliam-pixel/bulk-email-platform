@props([
    'title' => '',
    'value' => '0',
    'hint' => null,
    'trend' => null,
    'icon' => null,
])

<div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm hover:shadow-md transition duration-300">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $title }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ $value }}</p>
            @if($hint)
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
            @endif
        </div>
        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-300 grid place-items-center">
            @if($icon)
                {!! $icon !!}
            @else
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            @endif
        </div>
    </div>

    @if($trend)
        <div class="mt-4 inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded-full
                    {{ str_contains($trend, '-') ? 'text-rose-600 bg-rose-50 dark:bg-rose-500/10' : 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10' }}">
            <span>{{ $trend }}</span>
        </div>
    @endif
</div>
