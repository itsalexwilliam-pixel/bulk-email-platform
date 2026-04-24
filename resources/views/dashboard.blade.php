@extends('layouts.app')

@section('page_title', 'Dashboard')

@section('content')
@php
    $contactsCount = \App\Models\Contact::count();
    $campaignCount = \App\Models\Campaign::count();
    $emailsSent = \App\Models\EmailQueue::where('status', 'sent')->count();

    $openRate = 0;
    $clickRate = 0;
    if ($emailsSent > 0) {
        $openRate = round((\App\Models\EmailOpen::count() / max($emailsSent, 1)) * 100, 1);
        $clickRate = round((\App\Models\EmailClick::count() / max($emailsSent, 1)) * 100, 1);
    }

    $recentCampaigns = \App\Models\Campaign::latest()->take(6)->get(['id', 'name', 'subject', 'status', 'created_at']);
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <x-saas-stat-card title="Total Contacts" :value="$contactsCount" hint="Audience size"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5V4H2v16h5m10 0v-6h-4v6m4 0h-4"/></svg>' />
        <x-saas-stat-card title="Campaigns Sent" :value="$campaignCount" hint="Total campaigns"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>' />
        <x-saas-stat-card title="Emails Sent" :value="$emailsSent" hint="Delivered queue count"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8m-18 8h18a2 2 0 002-2V8a2 2 0 00-2-2H3a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>' />
        <x-saas-stat-card title="Open Rate" :value="$openRate . '%'" hint="From tracked opens" />
        <x-saas-stat-card title="Click Rate" :value="$clickRate . '%'" hint="From tracked clicks" />
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Emails Sent Over Time</h3>
            <canvas id="sentLineChart" height="120"></canvas>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Opens vs Clicks</h3>
            <canvas id="openClickBarChart" height="120"></canvas>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Recent Campaigns</h3>
            <a href="{{ route('campaigns.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">View all</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <th class="py-3 pr-4">Name</th>
                        <th class="py-3 pr-4">Subject</th>
                        <th class="py-3 pr-4">Status</th>
                        <th class="py-3">Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentCampaigns as $campaign)
                        <tr class="border-b border-slate-100 dark:border-slate-800/70">
                            <td class="py-3 pr-4 font-medium text-slate-800 dark:text-slate-100">{{ $campaign->name }}</td>
                            <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ $campaign->subject }}</td>
                            <td class="py-3 pr-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ ucfirst($campaign->status ?? 'draft') }}
                                </span>
                            </td>
                            <td class="py-3 text-slate-500 dark:text-slate-400">{{ optional($campaign->created_at)->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500 dark:text-slate-400">No campaigns yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const sentCtx = document.getElementById('sentLineChart');
    if (sentCtx) {
        new Chart(sentCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Emails Sent',
                    data: [18, 26, 31, 24, 40, 36, 52],
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.15)',
                    tension: 0.35,
                    fill: true
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    const openClickCtx = document.getElementById('openClickBarChart');
    if (openClickCtx) {
        new Chart(openClickCtx, {
            type: 'bar',
            data: {
                labels: ['Campaign A', 'Campaign B', 'Campaign C', 'Campaign D'],
                datasets: [
                    { label: 'Opens', data: [120, 90, 160, 110], backgroundColor: '#10b981' },
                    { label: 'Clicks', data: [42, 28, 58, 31], backgroundColor: '#3b82f6' }
                ]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });
    }
</script>
@endpush
