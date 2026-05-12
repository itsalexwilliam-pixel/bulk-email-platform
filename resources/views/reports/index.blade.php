@extends('layouts.app')

@section('page_title', 'Campaign Reports')

@section('content')
<div class="space-y-6">
    {{-- Tab nav --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-2 shadow-sm">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reports.single-email') }}"
               class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                Single Email Report
            </a>
            <a href="{{ route('reports.index') }}"
               class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium bg-indigo-600 text-white border border-indigo-600">
                Campaign Report
            </a>
            <a href="{{ route('reports.warmup') }}"
               class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                Warmup Report
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('reports.index') }}" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-5 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div>
                <label for="date_range" class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Date Range</label>
                <select id="date_range" name="date_range" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm px-3 py-2">
                    <option value="7d"     {{ ($filters['date_range'] ?? '30d') === '7d'     ? 'selected' : '' }}>Last 7 days</option>
                    <option value="30d"    {{ ($filters['date_range'] ?? '30d') === '30d'    ? 'selected' : '' }}>Last 30 days</option>
                    <option value="custom" {{ ($filters['date_range'] ?? '30d') === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>
            <div>
                <label for="from" class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">From</label>
                <input id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}"
                       class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm px-3 py-2">
            </div>
            <div>
                <label for="to" class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">To</label>
                <input id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}"
                       class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm px-3 py-2">
            </div>
            <div>
                <label for="campaign_id" class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Campaign</label>
                <select id="campaign_id" name="campaign_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm px-3 py-2">
                    <option value="">All campaigns</option>
                    @foreach($campaignOptions as $campaign)
                        <option value="{{ $campaign->id }}" {{ (int) ($filters['campaign_id'] ?? 0) === (int) $campaign->id ? 'selected' : '' }}>
                            {{ $campaign->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 inline-flex justify-center items-center rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2">
                    Apply
                </button>
                <a href="{{ route('reports.index') }}" class="flex-1 inline-flex justify-center items-center rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm px-4 py-2">
                    Reset
                </a>
            </div>
        </div>
        <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">
            Showing data from <strong>{{ $filters['from'] }}</strong> to <strong>{{ $filters['to'] }}</strong>
        </p>
    </form>

    {{-- Summary metrics --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400">Emails Sent</p>
            <p class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($metrics['sent']) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-950/30 p-5 shadow-sm">
            <p class="text-xs text-emerald-700 dark:text-emerald-300">Opens</p>
            <p class="mt-1 text-3xl font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($metrics['opens']) }}</p>
            <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $metrics['open_rate'] }}% open rate</p>
        </div>
        <div class="rounded-2xl border border-blue-200 bg-blue-50 dark:bg-blue-950/30 p-5 shadow-sm">
            <p class="text-xs text-blue-700 dark:text-blue-300">Clicks</p>
            <p class="mt-1 text-3xl font-bold text-blue-700 dark:text-blue-300">{{ number_format($metrics['clicks']) }}</p>
            <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">{{ $metrics['click_rate'] }}% click rate</p>
        </div>
        <div class="rounded-2xl border border-rose-200 bg-rose-50 dark:bg-rose-950/30 p-5 shadow-sm">
            <p class="text-xs text-rose-700 dark:text-rose-300">Unsubscribes</p>
            <p class="mt-1 text-3xl font-bold text-rose-700 dark:text-rose-300">{{ number_format($metrics['unsubscribes']) }}</p>
        </div>
    </div>

    @if($metrics['sent'] === 0)
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 text-center text-slate-500 dark:text-slate-400">
            No sent emails found for the selected filters.
        </div>
    @endif

    {{-- Charts --}}
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

    {{-- Campaign Performance table --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Campaign Performance</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <th class="py-2 pr-3">Campaign</th>
                        <th class="py-2 pr-3">Status</th>
                        <th class="py-2 pr-3 text-right">Sent</th>
                        <th class="py-2 pr-3 text-right">Opens</th>
                        <th class="py-2 pr-3 text-right">Clicks</th>
                        <th class="py-2 pr-3 text-right">Open Rate</th>
                        <th class="py-2 pr-3 text-right">Click Rate</th>
                        <th class="py-2 pr-3 text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($campaignRows as $row)
                        <tr class="border-b border-slate-100 dark:border-slate-800/70 hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                            <td class="py-2 pr-3 font-medium text-slate-800 dark:text-slate-100">{{ $row->campaign_name }}</td>
                            <td class="py-2 pr-3">
                                @if($row->campaign_status)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs
                                        @if(in_array($row->campaign_status, ['sent','completed'])) bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300
                                        @elseif($row->campaign_status === 'sending') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300
                                        @elseif($row->campaign_status === 'scheduled') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300
                                        @else bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 @endif
                                    ">{{ ucfirst($row->campaign_status) }}</span>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="py-2 pr-3 text-right">{{ number_format($row->sent_count) }}</td>
                            <td class="py-2 pr-3 text-right">{{ number_format($row->open_count) }}</td>
                            <td class="py-2 pr-3 text-right">{{ number_format($row->click_count) }}</td>
                            <td class="py-2 pr-3 text-right">
                                <span class="{{ $row->open_rate >= 20 ? 'text-emerald-600 dark:text-emerald-400 font-medium' : '' }}">
                                    {{ $row->open_rate }}%
                                </span>
                            </td>
                            <td class="py-2 pr-3 text-right">
                                <span class="{{ $row->click_rate >= 2 ? 'text-blue-600 dark:text-blue-400 font-medium' : '' }}">
                                    {{ $row->click_rate }}%
                                </span>
                            </td>
                            <td class="py-2 pr-3 text-right">
                                @if($row->campaign_id)
                                    <a href="{{ route('reports.campaign.detail', $row->campaign_id) }}"
                                       class="inline-flex items-center rounded-md border border-slate-300 dark:border-slate-700 px-2.5 py-1 text-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                        View →
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500">No campaign data for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- UTM tables --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Top UTM Sources</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                            <th class="py-2 pr-3">UTM Source</th>
                            <th class="py-2 pr-3">UTM Medium</th>
                            <th class="py-2 pr-3 text-right">Clicks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($utmSourceRows as $row)
                            <tr class="border-b border-slate-100 dark:border-slate-800/70">
                                <td class="py-2 pr-3">{{ $row->utm_source }}</td>
                                <td class="py-2 pr-3">{{ $row->utm_medium }}</td>
                                <td class="py-2 pr-3 text-right">{{ $row->total_clicks }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-5 text-center text-slate-500">No UTM source data.</td></tr>
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
                            <th class="py-2 pr-3 text-right">Clicks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($utmCampaignRows as $row)
                            <tr class="border-b border-slate-100 dark:border-slate-800/70">
                                <td class="py-2 pr-3">{{ $row->utm_campaign }}</td>
                                <td class="py-2 pr-3 text-right">{{ $row->total_clicks }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-5 text-center text-slate-500">No UTM campaign data.</td></tr>
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
    const labels     = @json($chart['labels'] ?? []);
    const sentData   = @json($chart['sent']   ?? []);
    const opensData  = @json($chart['opens']  ?? []);
    const clicksData = @json($chart['clicks'] ?? []);

    const sentCtx = document.getElementById('sentOverTimeChart');
    if (sentCtx && typeof Chart !== 'undefined') {
        new Chart(sentCtx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Sent',
                    data: sentData,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.15)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: labels.length > 14 ? 2 : 4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: true } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
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
                    { label: 'Opens',  data: opensData,  backgroundColor: '#10b981' },
                    { label: 'Clicks', data: clicksData, backgroundColor: '#3b82f6' }
                ]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    const dateRange = document.getElementById('date_range');
    const fromInput = document.getElementById('from');
    const toInput   = document.getElementById('to');

    function syncCustomDate() {
        const isCustom = dateRange?.value === 'custom';
        if (fromInput) fromInput.disabled = !isCustom;
        if (toInput)   toInput.disabled   = !isCustom;
    }

    dateRange?.addEventListener('change', syncCustomDate);
    syncCustomDate();
})();
</script>
@endpush
