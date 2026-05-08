@extends('layouts.app')

@section('page_title', 'SMTP Health')

@section('content')
<div class="space-y-6">

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm flex items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">SMTP Health Dashboard</h2>
            <p class="text-sm text-slate-500 mt-0.5">Per-server sending stats for today and the last 7 days.</p>
        </div>
        <a href="{{ route('smtp.index') }}" class="text-sm text-indigo-600 hover:underline">← Back to SMTP</a>
    </div>

    @if($stats->isEmpty())
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 text-center text-slate-400 shadow-sm">
            <p class="text-sm">No SMTP servers configured yet.</p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($stats as $s)
                @php
                    $rate = $s['success_rate'];
                    $rateColor = $rate === null ? 'text-slate-400'
                        : ($rate >= 90 ? 'text-emerald-600 dark:text-emerald-400'
                        : ($rate >= 70 ? 'text-amber-600 dark:text-amber-400'
                        : 'text-rose-600 dark:text-rose-400'));
                @endphp
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $s['name'] }}</h3>
                                @if($s['is_active'])
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">ACTIVE</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">INACTIVE</span>
                                @endif
                                @if($s['priority'])
                                    <span class="text-xs text-slate-400">Priority {{ $s['priority'] }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $s['host'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold {{ $rateColor }}">
                                {{ $rate !== null ? $rate . '%' : '—' }}
                            </p>
                            <p class="text-xs text-slate-400">success rate today</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3 text-center">
                            <p class="text-xs text-slate-500 mb-1">Sent Today</p>
                            <p class="text-lg font-bold text-slate-800 dark:text-white">{{ number_format($s['sent_today']) }}</p>
                        </div>
                        <div class="rounded-xl bg-rose-50/60 dark:bg-rose-950/20 p-3 text-center">
                            <p class="text-xs text-rose-500 mb-1">Failed Today</p>
                            <p class="text-lg font-bold text-rose-700 dark:text-rose-300">{{ number_format($s['fail_today']) }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3 text-center">
                            <p class="text-xs text-slate-500 mb-1">Sent 7 Days</p>
                            <p class="text-lg font-bold text-slate-800 dark:text-white">{{ number_format($s['total_sent_7']) }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3 text-center">
                            <p class="text-xs text-slate-500 mb-1">Last Used</p>
                            <p class="text-xs font-medium text-slate-600 dark:text-slate-300 mt-1.5">{{ $s['last_used_at'] }}</p>
                        </div>
                    </div>

                    @if($s['daily_limit'] !== null)
                        <div>
                            <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                                <span>Daily limit usage</span>
                                <span>{{ number_format($s['sent_today']) }} / {{ number_format($s['daily_limit']) }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden">
                                <div class="h-2 rounded-full {{ $s['daily_usage_pct'] >= 90 ? 'bg-rose-500' : ($s['daily_usage_pct'] >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }} transition-all"
                                     style="width: {{ $s['daily_usage_pct'] }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
