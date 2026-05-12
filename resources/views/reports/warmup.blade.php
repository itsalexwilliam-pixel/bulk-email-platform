@extends('layouts.app')

@section('page_title', 'Warmup Report')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-2 shadow-sm">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reports.single-email') }}"
               class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                Single Email Report
            </a>
            <a href="{{ route('reports.index') }}"
               class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                Campaign Report
            </a>
            <a href="{{ route('reports.warmup') }}"
               class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium bg-indigo-600 text-white border border-indigo-600">
                Warmup Report
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400">Warmup Campaigns</p>
            <p class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($summary['campaigns_in_warmup'] ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-blue-200 bg-blue-50 dark:bg-blue-950/30 p-5 shadow-sm">
            <p class="text-xs text-blue-700 dark:text-blue-300">Today Sent</p>
            <p class="mt-1 text-3xl font-bold text-blue-700 dark:text-blue-300">{{ number_format($summary['total_today_sent'] ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 dark:bg-amber-950/30 p-5 shadow-sm">
            <p class="text-xs text-amber-700 dark:text-amber-300">Today Warmup Cap</p>
            <p class="mt-1 text-3xl font-bold text-amber-700 dark:text-amber-300">{{ number_format($summary['total_today_cap'] ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-950/30 p-5 shadow-sm">
            <p class="text-xs text-emerald-700 dark:text-emerald-300">Avg Saturation</p>
            <p class="mt-1 text-3xl font-bold text-emerald-700 dark:text-emerald-300">{{ $summary['avg_saturation_percent'] ?? 0 }}%</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Warmup Campaign Details</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <th class="py-2 pr-3">Campaign</th>
                        <th class="py-2 pr-3">Status</th>
                        <th class="py-2 pr-3 text-right">Warmup Day</th>
                        <th class="py-2 pr-3 text-right">Daily Cap</th>
                        <th class="py-2 pr-3 text-right">Today Sent</th>
                        <th class="py-2 pr-3 text-right">Saturation</th>
                        <th class="py-2 pr-3 text-right">Queued</th>
                        <th class="py-2 pr-3 text-right">Total Sent</th>
                        <th class="py-2 pr-3 text-right">Failed</th>
                        <th class="py-2 pr-3">Warmup Started</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr class="border-b border-slate-100 dark:border-slate-800/70 hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                            <td class="py-2 pr-3 font-medium text-slate-800 dark:text-slate-100">{{ $row->name }}</td>
                            <td class="py-2 pr-3 capitalize">{{ $row->status }}</td>
                            <td class="py-2 pr-3 text-right">{{ $row->warmup_day }}</td>
                            <td class="py-2 pr-3 text-right">{{ number_format($row->warmup_cap) }}</td>
                            <td class="py-2 pr-3 text-right">{{ number_format($row->today_sent) }}</td>
                            <td class="py-2 pr-3 text-right">{{ $row->saturation_percent }}%</td>
                            <td class="py-2 pr-3 text-right">{{ number_format($row->queued_count) }}</td>
                            <td class="py-2 pr-3 text-right">{{ number_format($row->sent_count) }}</td>
                            <td class="py-2 pr-3 text-right">{{ number_format($row->failed_count) }}</td>
                            <td class="py-2 pr-3">{{ optional($row->warmup_started_at)->format('Y-m-d H:i') ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                No warmup-enabled campaigns found for this account.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
