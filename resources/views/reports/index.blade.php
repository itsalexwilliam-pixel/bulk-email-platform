@extends('layouts.app')

@section('page_title', 'Reports')

@section('content')
<div class="space-y-6">
    <form method="GET" action="{{ route('reports.index') }}" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-5 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div>
                <label for="date_range" class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Date Range</label>
                <select id="date_range" name="date_range" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
                    <option value="7d" {{ ($filters['date_range'] ?? '7d') === '7d' ? 'selected' : '' }}>Last 7 days</option>
                    <option value="30d" {{ ($filters['date_range'] ?? '7d') === '30d' ? 'selected' : '' }}>Last 30 days</option>
                    <option value="custom" {{ ($filters['date_range'] ?? '7d') === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>

            <div>
                <label for="from" class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">From</label>
                <input id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
            </div>

            <div>
                <label for="to" class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">To</label>
                <input id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
            </div>

            <div>
                <label for="campaign_id" class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Campaign</label>
                <select id="campaign_id" name="campaign_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
                    <option value="">All campaigns</option>
                    @foreach($campaignOptions as $campaign)
                        <option value="{{ $campaign->id }}" {{ (int) ($filters['campaign_id'] ?? 0) === (int) $campaign->id ? 'selected' : '' }}>
                            {{ $campaign->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full inline-flex justify-center items-center rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2">
                    Apply Filters
                </button>
            </div>
        </div>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-saas-stat-card title="Total Emails Sent" :value="$metrics['sent']" />
        <x-saas-stat-card title="Opens" :value="$metrics['opens'] . ' (' . $metrics['open_rate'] . '%)'" />
        <x-saas-stat-card title="Clicks" :value="$metrics['clicks'] . ' (' . $metrics['click_rate'] . '%)'" />
        <x-saas-stat-card title="Unsubscribes" :value="$metrics['unsubscribes']" />
    </div>

    @if(($metrics['sent'] ?? 0) === 0 && ($metrics['opens'] ?? 0) === 0 && ($metrics['clicks'] ?? 0) === 0)
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 text-center text-slate-500 dark:text-slate-300">
            No data available for the selected filters.
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Emails Sent Over Time</h3>
            <canvas id="sentOverTimeChart" height="110"></canvas>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Opens vs Clicks</h3>
            <canvas id="opensClicksChart" height="110"></canvas>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Campaign Performance</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <th class="py-2 pr-3">Campaign</th>
                        <th class="py-2 pr-3">Sent</th>
                        <th class="py-2 pr-3">Opens</th>
                        <th class="py-2 pr-3">Clicks</th>
                        <th class="py-2 pr-3">Open Rate</th>
                        <th class="py-2 pr-3">Click Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($campaignRows as $row)
                        <tr class="border-b border-slate-100 dark:border-slate-800/70">
                            <td class="py-2 pr-3">{{ $row->campaign_name }}</td>
                            <td class="py-2 pr-3">{{ $row->sent_count }}</td>
                            <td class="py-2 pr-3">{{ $row->open_count }}</td>
                            <td class="py-2 pr-3">{{ $row->click_count }}</td>
                            <td class="py-2 pr-3">{{ $row->open_rate }}%</td>
                            <td class="py-2 pr-3">{{ $row->click_rate }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center text-slate-500">No campaign metrics available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Top UTM Sources</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                            <th class="py-2 pr-3">UTM Source</th>
                            <th class="py-2 pr-3">UTM Medium</th>
                            <th class="py-2 pr-3">Clicks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($utmSourceRows as $row)
                            <tr class="border-b border-slate-100 dark:border-slate-800/70">
                                <td class="py-2 pr-3">{{ $row->utm_source }}</td>
                                <td class="py-2 pr-3">{{ $row->utm_medium }}</td>
                                <td class="py-2 pr-3">{{ $row->total_clicks }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-5 text-center text-slate-500">No UTM source data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Top UTM Campaigns</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                            <th class="py-2 pr-3">UTM Campaign</th>
                            <th class="py-2 pr-3">Clicks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($utmCampaignRows as $row)
                            <tr class="border-b border-slate-100 dark:border-slate-800/70">
                                <td class="py-2 pr-3">{{ $row->utm_campaign }}</td>
                                <td class="py-2 pr-3">{{ $row->total_clicks }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-5 text-center text-slate-500">No UTM campaign data available.</td>
                            </tr>
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
(function () {
    const labels = @json($chart['labels'] ?? []);
    const sentData = @json($chart['sent'] ?? []);
    const opensData = @json($chart['opens'] ?? []);
    const clicksData = @json($chart['clicks'] ?? []);

    const sentCtx = document.getElementById('sentOverTimeChart');
    if (sentCtx && typeof Chart !== 'undefined') {
        new Chart(sentCtx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Sent',
                        data: sentData,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99,102,241,0.18)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: true } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    const opensClicksCtx = document.getElementById('opensClicksChart');
    if (opensClicksCtx && typeof Chart !== 'undefined') {
        new Chart(opensClicksCtx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Opens',
                        data: opensData,
                        backgroundColor: '#10b981'
                    },
                    {
                        label: 'Clicks',
                        data: clicksData,
                        backgroundColor: '#3b82f6'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    const dateRange = document.getElementById('date_range');
    const fromInput = document.getElementById('from');
    const toInput = document.getElementById('to');

    function syncCustomDateEnabled() {
        const isCustom = dateRange && dateRange.value === 'custom';
        if (fromInput) fromInput.disabled = !isCustom;
        if (toInput) toInput.disabled = !isCustom;
    }

    dateRange?.addEventListener('change', syncCustomDateEnabled);
    syncCustomDateEnabled();
})();
</script>
@endpush
