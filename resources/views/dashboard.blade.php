@extends('layouts.app')

@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Welcome back 👋</h2>
                <p class="text-sm text-slate-500 mt-0.5">Here's what's happening with your email platform today.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('campaigns.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Campaign
                </a>
                <a href="{{ route('single-email.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Send Email
                </a>
                <a href="{{ route('import.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import Contacts
                </a>
            </div>
        </div>
    </div>

    {{-- ── Stat Cards Row 1: Email Activity ───────────────────────────────── --}}
    <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3">Email Activity</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4">

            {{-- Emails Sent (Total) --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Sent</span>
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8m-18 8h18a2 2 0 002-2V8a2 2 0 00-2-2H3a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    </span>
                </div>
                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($emailsSentTotal) }}</p>
                <p class="text-xs text-slate-400 mt-1">All time</p>
            </div>

            {{-- Emails Sent This Month --}}
            <div class="rounded-2xl border border-emerald-200 dark:border-emerald-800/50 bg-emerald-50/50 dark:bg-emerald-950/20 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400">This Month</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </span>
                </div>
                <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($emailsSentMonth) }}</p>
                <p class="text-xs text-emerald-600/70 dark:text-emerald-400/60 mt-1">{{ now()->format('F Y') }}</p>
            </div>

            {{-- Open Rate --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Open Rate</span>
                    <span class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-sky-500 dark:text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </span>
                </div>
                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $openRate }}%</p>
                <p class="text-xs text-slate-400 mt-1">Tracked opens</p>
            </div>

            {{-- Click Rate --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Click Rate</span>
                    <span class="w-8 h-8 rounded-xl bg-violet-50 dark:bg-violet-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-violet-500 dark:text-violet-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/></svg>
                    </span>
                </div>
                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $clickRate }}%</p>
                <p class="text-xs text-slate-400 mt-1">Tracked clicks</p>
            </div>

            {{-- Failed Emails --}}
            <div class="rounded-2xl border {{ $emailsFailed > 0 ? 'border-rose-200 dark:border-rose-800/50 bg-rose-50/50 dark:bg-rose-950/20' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900' }} p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium {{ $emailsFailed > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400' }}">Failed</span>
                    <span class="w-8 h-8 rounded-xl {{ $emailsFailed > 0 ? 'bg-rose-100 dark:bg-rose-500/20' : 'bg-slate-100 dark:bg-slate-800' }} flex items-center justify-center">
                        <svg class="w-4 h-4 {{ $emailsFailed > 0 ? 'text-rose-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    </span>
                </div>
                <p class="text-2xl font-bold {{ $emailsFailed > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">{{ number_format($emailsFailed) }}</p>
                <p class="text-xs {{ $emailsFailed > 0 ? 'text-rose-400' : 'text-slate-400' }} mt-1">
                    {{ $emailsPending > 0 ? $emailsPending . ' pending' : 'No failures' }}
                </p>
            </div>
        </div>
    </div>

    {{-- ── Stat Cards Row 2: Audience & Campaigns ─────────────────────────── --}}
    <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3">Audience & Campaigns</p>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">Total Contacts</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($totalContacts) }}</p>
                <a href="{{ route('contacts.index') }}" class="text-xs text-indigo-600 hover:underline mt-1 block">Manage →</a>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">Groups / Lists</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($totalGroups) }}</p>
                <a href="{{ route('groups.index') }}" class="text-xs text-indigo-600 hover:underline mt-1 block">Manage →</a>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">Campaigns Sent</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($campaignsSent) }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ $campaignsDraft }} draft / scheduled</p>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">Active SMTP Servers</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($totalSmtp) }}</p>
                <a href="{{ route('smtp.index') }}" class="text-xs text-indigo-600 hover:underline mt-1 block">Manage →</a>
            </div>
        </div>
    </div>

    {{-- ── Charts ──────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Emails Sent — Last 7 Days</h3>
                <span class="text-xs text-slate-400">{{ now()->subDays(6)->format('d M') }} – {{ now()->format('d M') }}</span>
            </div>
            <canvas id="sentLineChart" height="120"></canvas>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Opens vs Clicks (Top 5 Campaigns)</h3>
            </div>
            <canvas id="openClickBarChart" height="120"></canvas>
        </div>
    </div>

    {{-- ── Additional Charts Row ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- 30-day trend --}}
        <div class="xl:col-span-2 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Emails Sent — Last 30 Days</h3>
                <span class="text-xs text-slate-400">{{ now()->subDays(29)->format('d M') }} – {{ now()->format('d M') }}</span>
            </div>
            <canvas id="trend30Chart" height="90"></canvas>
        </div>

        {{-- Campaign status donut --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Campaign Status</h3>
            @if(array_sum($donutValues) > 0)
                <canvas id="campaignDonut" height="160"></canvas>
            @else
                <div class="flex flex-col items-center justify-center h-40 text-slate-400 text-xs">No campaigns yet.</div>
            @endif
        </div>
    </div>

    {{-- ── Top Contacts by Opens ────────────────────────────────────────────── --}}
    @if(count($topContactValues) > 0)
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Top 5 Most Engaged Contacts (Opens)</h3>
        <canvas id="topContactsChart" height="80"></canvas>
    </div>
    @endif

    {{-- ── Recent Campaigns + Failed Emails ───────────────────────────────── --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- Recent Campaigns --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Recent Campaigns</h3>
                <a href="{{ route('campaigns.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">View all →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                            <th class="pb-2 pr-4 font-medium">Name</th>
                            <th class="pb-2 pr-4 font-medium">Status</th>
                            <th class="pb-2 font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($recentCampaigns as $campaign)
                            <tr>
                                <td class="py-2.5 pr-4">
                                    <a href="{{ route('campaigns.edit', $campaign) }}" class="font-medium text-slate-800 dark:text-slate-100 hover:text-indigo-600 truncate block max-w-[180px]">
                                        {{ $campaign->name }}
                                    </a>
                                    <span class="text-xs text-slate-400">{{ $campaign->subject }}</span>
                                </td>
                                <td class="py-2.5 pr-4">
                                    @php
                                        $statusColors = [
                                            'draft'      => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
                                            'scheduled'  => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                            'sending'    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                            'sent'       => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                            'completed'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                            'paused'     => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
                                            'failed'     => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                                        ];
                                        $cls = $statusColors[$campaign->status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300';
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $cls }}">
                                        {{ ucfirst($campaign->status ?? 'draft') }}
                                    </span>
                                </td>
                                <td class="py-2.5 text-xs text-slate-400">{{ optional($campaign->created_at)->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-slate-400 text-xs">
                                    No campaigns yet.
                                    <a href="{{ route('campaigns.create') }}" class="text-indigo-600 hover:underline ml-1">Create one →</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Failed Emails --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                    Recent Failed Emails
                    @if($emailsFailed > 0)
                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-300">{{ $emailsFailed }} total</span>
                    @endif
                </h3>
                <a href="{{ route('reports.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Reports →</a>
            </div>

            @if($recentFailed->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-slate-400 dark:text-slate-500">
                    <svg class="w-10 h-10 mb-2 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">All good — no failed emails!</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($recentFailed as $failed)
                        <div class="rounded-xl border border-rose-100 dark:border-rose-900/40 bg-rose-50/50 dark:bg-rose-950/20 px-3 py-2.5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-100 truncate">{{ $failed->email }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ $failed->subject }}</p>
                                </div>
                                <span class="shrink-0 text-xs text-slate-400">{{ optional($failed->updated_at)->diffForHumans() }}</span>
                            </div>
                            @if($failed->last_error)
                                <p class="mt-1 text-xs text-rose-500 dark:text-rose-400 truncate">{{ $failed->last_error }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const labelColor = isDark ? '#94a3b8' : '#64748b';

    const sharedScaleOpts = {
        grid: { color: gridColor },
        ticks: { color: labelColor, font: { size: 11 } }
    };

    // ── Sent line chart ───────────────────────────────────────────────────────
    const sentCtx = document.getElementById('sentLineChart');
    if (sentCtx) {
        new Chart(sentCtx, {
            type: 'line',
            data: {
                labels: @json($sentChartLabels),
                datasets: [{
                    label: 'Emails Sent',
                    data: @json($sentChartValues),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.12)',
                    tension: 0.35,
                    fill: true,
                    pointBackgroundColor: '#4f46e5',
                    pointRadius: 4,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: sharedScaleOpts,
                    y: { ...sharedScaleOpts, beginAtZero: true }
                }
            }
        });
    }

    // ── 30-day trend line chart ───────────────────────────────────────────────
    const trend30Ctx = document.getElementById('trend30Chart');
    if (trend30Ctx) {
        new Chart(trend30Ctx, {
            type: 'line',
            data: {
                labels: @json($trend30Labels),
                datasets: [{
                    label: 'Emails Sent',
                    data: @json($trend30Values),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.10)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 2,
                    pointBackgroundColor: '#10b981',
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        ...sharedScaleOpts,
                        ticks: {
                            ...sharedScaleOpts.ticks,
                            maxTicksLimit: 10,
                        }
                    },
                    y: { ...sharedScaleOpts, beginAtZero: true }
                }
            }
        });
    }

    // ── Campaign status donut ─────────────────────────────────────────────────
    const donutCtx = document.getElementById('campaignDonut');
    if (donutCtx) {
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: @json($donutLabels),
                datasets: [{
                    data: @json($donutValues),
                    backgroundColor: @json($donutColors),
                    borderWidth: 2,
                    borderColor: isDark ? '#1e293b' : '#ffffff',
                }]
            },
            options: {
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: labelColor, font: { size: 11 }, padding: 10, boxWidth: 12 }
                    }
                }
            }
        });
    }

    // ── Top contacts by opens ─────────────────────────────────────────────────
    const topContactsCtx = document.getElementById('topContactsChart');
    if (topContactsCtx) {
        new Chart(topContactsCtx, {
            type: 'bar',
            data: {
                labels: @json($topContactLabels),
                datasets: [{
                    label: 'Opens',
                    data: @json($topContactValues),
                    backgroundColor: 'rgba(139,92,246,0.75)',
                    borderRadius: 6,
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { ...sharedScaleOpts, beginAtZero: true },
                    y: sharedScaleOpts,
                }
            }
        });
    }

    // ── Opens vs Clicks bar chart ─────────────────────────────────────────────
    const openClickCtx = document.getElementById('openClickBarChart');
    if (openClickCtx) {
        new Chart(openClickCtx, {
            type: 'bar',
            data: {
                labels: @json($openClickLabels),
                datasets: [
                    {
                        label: 'Opens',
                        data: @json($openClickOpens),
                        backgroundColor: 'rgba(16,185,129,0.75)',
                        borderRadius: 4,
                    },
                    {
                        label: 'Clicks',
                        data: @json($openClickClicks),
                        backgroundColor: 'rgba(59,130,246,0.75)',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: { color: labelColor, font: { size: 11 } }
                    }
                },
                scales: {
                    x: sharedScaleOpts,
                    y: { ...sharedScaleOpts, beginAtZero: true }
                }
            }
        });
    }
</script>
@endpush
