@extends('layouts.app')

@section('page_title', 'Campaigns')

@section('content')
@php
    $total = $campaigns->total();
    $sent = \App\Models\Campaign::where('status', 'sent')->count();
    $scheduled = \App\Models\Campaign::where('status', 'scheduled')->count();
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-saas-stat-card title="Total Campaigns" :value="$total" />
        <x-saas-stat-card title="Sent" :value="$sent" />
        <x-saas-stat-card title="Scheduled" :value="$scheduled" />
    </div>

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Campaign List</h2>
            <a href="{{ route('campaigns.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                Create Campaign
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr class="text-left text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Subject</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Scheduled At</th>
                        <th class="py-3 px-4">Contacts</th>
                        <th class="py-3 px-4">Stats (Queue/Sent/Opened/Failed)</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($campaigns as $campaign)
                        <tr class="border-t border-slate-100 dark:border-slate-800 hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-100">{{ $campaign->name }}</td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ $campaign->subject }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 uppercase">
                                    {{ $campaign->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                                {{ $campaign->scheduled_at ? \Illuminate\Support\Carbon::parse($campaign->scheduled_at)->format('Y-m-d H:i') : '-' }}
                            </td>
                            <td class="py-3 px-4">{{ $campaign->contacts_count }}</td>
                            <td class="py-3 px-4">
                                <div class="text-xs text-slate-700 dark:text-slate-200 space-y-1" id="campaign-stats-{{ $campaign->id }}">
                                    <div>Queued: <strong data-k="queued">{{ $campaign->queued_count ?? 0 }}</strong></div>
                                    <div>Sent: <strong data-k="sent">{{ $campaign->sent_count ?? 0 }}</strong></div>
                                    <div>Opened: <strong data-k="opened">{{ $campaign->opened_count ?? 0 }}</strong></div>
                                    <div>Failed: <strong data-k="failed">{{ $campaign->failed_count ?? 0 }}</strong></div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-end gap-2">
                                    @if(in_array($campaign->status, ['draft', 'scheduled']))
                                        <form action="{{ route('campaigns.send', $campaign) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-medium hover:bg-emerald-200 transition"
                                                    onclick="return confirm('Start sending this campaign now?')">
                                                Start Send
                                            </button>
                                        </form>
                                    @elseif($campaign->status === 'sending')
                                        <form action="{{ route('campaigns.pause', $campaign) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg bg-amber-100 text-amber-700 text-xs font-medium hover:bg-amber-200 transition">
                                                Pause
                                            </button>
                                        </form>
                                    @elseif($campaign->status === 'paused')
                                        <form action="{{ route('campaigns.resume', $campaign) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-medium hover:bg-emerald-200 transition">
                                                Resume
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('campaigns.send-test-email', $campaign) }}" method="POST" class="inline-flex items-center gap-2">
                                        @csrf
                                        <input type="email" name="test_email" required placeholder="test@example.com"
                                               class="w-36 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-2 py-1.5 text-xs">
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-lg bg-cyan-100 text-cyan-700 text-xs font-medium hover:bg-cyan-200 transition">
                                            Test Camp
                                        </button>
                                    </form>

                                    <a href="{{ route('campaigns.edit', $campaign) }}"
                                       class="px-3 py-1.5 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-medium hover:bg-indigo-200 transition">
                                        Edit
                                    </a>
                                    <button type="button"
                                            class="px-3 py-1.5 rounded-lg bg-blue-100 text-blue-700 text-xs font-medium hover:bg-blue-200 transition"
                                            onclick="toggleLogs({{ $campaign->id }})">
                                        Live Logs
                                    </button>
                                    <form action="{{ route('campaigns.destroy', $campaign) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-lg bg-rose-100 text-rose-700 text-xs font-medium hover:bg-rose-200 transition"
                                                onclick="return confirm('Delete this campaign?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr id="logs-row-{{ $campaign->id }}" class="hidden border-t border-slate-100 dark:border-slate-800">
                            <td colspan="7" class="px-4 py-3">
                                <div class="rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                                    <div class="text-sm font-semibold mb-2">Live Sending Logs (Campaign #{{ $campaign->id }})</div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-xs">
                                            <thead>
                                                <tr class="text-left text-slate-500">
                                                    <th class="py-2 px-2">#</th>
                                                    <th class="py-2 px-2">Email</th>
                                                    <th class="py-2 px-2">Status</th>
                                                    <th class="py-2 px-2">Attempts</th>
                                                    <th class="py-2 px-2">Last Error</th>
                                                    <th class="py-2 px-2">Updated</th>
                                                </tr>
                                            </thead>
                                            <tbody id="logs-body-{{ $campaign->id }}">
                                                <tr><td colspan="6" class="py-2 px-2 text-slate-500">No logs loaded.</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">No campaigns found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $campaigns->links() }}
    </div>
</div>

<script>
    const logRefreshTimers = {};

    async function fetchLiveStats(campaignId) {
        const response = await fetch(`/campaigns/${campaignId}/live-stats`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!response.ok) return;
        const data = await response.json();

        const statsWrap = document.getElementById(`campaign-stats-${campaignId}`);
        if (statsWrap && data.stats) {
            statsWrap.querySelector('[data-k="queued"]').textContent = data.stats.queued ?? 0;
            statsWrap.querySelector('[data-k="sent"]').textContent = data.stats.sent ?? 0;
            statsWrap.querySelector('[data-k="opened"]').textContent = data.stats.opened ?? 0;
            statsWrap.querySelector('[data-k="failed"]').textContent = data.stats.failed ?? 0;
        }

        const logsBody = document.getElementById(`logs-body-${campaignId}`);
        if (logsBody) {
            if (!data.logs || data.logs.length === 0) {
                logsBody.innerHTML = `<tr><td colspan="6" class="py-2 px-2 text-slate-500">No logs yet.</td></tr>`;
            } else {
                logsBody.innerHTML = data.logs.map(row => `
                    <tr class="border-t border-slate-100 dark:border-slate-800">
                        <td class="py-2 px-2">${row.id}</td>
                        <td class="py-2 px-2">${row.email}</td>
                        <td class="py-2 px-2"><span class="uppercase">${row.status}</span></td>
                        <td class="py-2 px-2">${row.attempts ?? 0}</td>
                        <td class="py-2 px-2">${row.last_error ?? '-'}</td>
                        <td class="py-2 px-2">${row.updated_at ?? '-'}</td>
                    </tr>
                `).join('');
            }
        }
    }

    function toggleLogs(campaignId) {
        const row = document.getElementById(`logs-row-${campaignId}`);
        if (!row) return;

        row.classList.toggle('hidden');

        if (!row.classList.contains('hidden')) {
            fetchLiveStats(campaignId);

            if (!logRefreshTimers[campaignId]) {
                logRefreshTimers[campaignId] = setInterval(() => fetchLiveStats(campaignId), 7000);
            }
        } else if (logRefreshTimers[campaignId]) {
            clearInterval(logRefreshTimers[campaignId]);
            delete logRefreshTimers[campaignId];
        }
    }
</script>
@endsection
