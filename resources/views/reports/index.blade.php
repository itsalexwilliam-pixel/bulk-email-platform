@extends('layouts.app')

@section('page_title', 'Reports')

@section('content')
@php
    $opens = \App\Models\EmailOpen::count();
    $clicks = \App\Models\EmailClick::count();
    $unsubscribes = \App\Models\Unsubscribe::count();

    $recentOpens = \App\Models\EmailOpen::latest()->take(8)->get();
    $recentClicks = \App\Models\EmailClick::latest()->take(8)->get();
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-saas-stat-card title="Total Opens" :value="$opens" />
        <x-saas-stat-card title="Total Clicks" :value="$clicks" />
        <x-saas-stat-card title="Unsubscribes" :value="$unsubscribes" />
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Engagement Overview</h3>
            <canvas id="engagementChart" height="120"></canvas>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Unsubscribe Trend</h3>
            <canvas id="unsubscribeChart" height="120"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Recent Opens</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                            <th class="py-2 pr-3">ID</th>
                            <th class="py-2 pr-3">Opened At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOpens as $item)
                            <tr class="border-b border-slate-100 dark:border-slate-800/70">
                                <td class="py-2 pr-3">{{ $item->id }}</td>
                                <td class="py-2 pr-3">{{ optional($item->created_at)->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-5 text-center text-slate-500">No open events yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Recent Clicks</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                            <th class="py-2 pr-3">ID</th>
                            <th class="py-2 pr-3">Clicked At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentClicks as $item)
                            <tr class="border-b border-slate-100 dark:border-slate-800/70">
                                <td class="py-2 pr-3">{{ $item->id }}</td>
                                <td class="py-2 pr-3">{{ optional($item->created_at)->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-5 text-center text-slate-500">No click events yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const engagementCtx = document.getElementById('engagementChart');
    if (engagementCtx) {
        new Chart(engagementCtx, {
            type: 'bar',
            data: {
                labels: ['Opens', 'Clicks', 'Unsubscribes'],
                datasets: [{
                    data: [{{ $opens }}, {{ $clicks }}, {{ $unsubscribes }}],
                    backgroundColor: ['#10b981', '#3b82f6', '#ef4444']
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    const unsubCtx = document.getElementById('unsubscribeChart');
    if (unsubCtx) {
        new Chart(unsubCtx, {
            type: 'line',
            data: {
                labels: ['W1', 'W2', 'W3', 'W4'],
                datasets: [{
                    label: 'Unsubscribes',
                    data: [2, 5, 3, 6],
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,0.15)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });
    }
</script>
@endpush
