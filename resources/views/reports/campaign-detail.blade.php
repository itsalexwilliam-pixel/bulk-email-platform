@extends('layouts.app')

@section('page_title', 'Campaign Report — ' . $campaign->name)

@section('content')
<div class="space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
        <a href="{{ route('reports.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Campaign Reports</a>
        <span>/</span>
        <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $campaign->name }}</span>
    </div>

    {{-- Campaign header --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $campaign->name }}</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Subject: <span class="text-slate-700 dark:text-slate-200">{{ $campaign->subject }}</span>
                    &nbsp;·&nbsp;
                    Status:
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs
                        @if(in_array($campaign->status, ['sent','completed'])) bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300
                        @elseif($campaign->status === 'sending') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300
                        @elseif($campaign->status === 'scheduled') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300
                        @else bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 @endif
                    ">{{ ucfirst($campaign->status) }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('campaigns.edit', $campaign) }}"
                   class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Edit Campaign
                </a>
                <a href="{{ route('reports.index') }}"
                   class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    ← Back
                </a>
            </div>
        </div>
    </div>

    {{-- Metrics grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400">Sent</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($totalSent) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-950/30 p-4 shadow-sm">
            <p class="text-xs text-emerald-700 dark:text-emerald-300">Opens</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($totalOpens) }}</p>
            <p class="text-xs mt-0.5 text-emerald-600 dark:text-emerald-400">{{ $openRate }}%</p>
        </div>
        <div class="rounded-2xl border border-blue-200 bg-blue-50 dark:bg-blue-950/30 p-4 shadow-sm">
            <p class="text-xs text-blue-700 dark:text-blue-300">Clicks</p>
            <p class="mt-1 text-2xl font-bold text-blue-700 dark:text-blue-300">{{ number_format($totalClicks) }}</p>
            <p class="text-xs mt-0.5 text-blue-600 dark:text-blue-400">{{ $clickRate }}%</p>
        </div>
        <div class="rounded-2xl border border-rose-200 bg-rose-50 dark:bg-rose-950/30 p-4 shadow-sm">
            <p class="text-xs text-rose-700 dark:text-rose-300">Unsubscribes</p>
            <p class="mt-1 text-2xl font-bold text-rose-700 dark:text-rose-300">{{ number_format($totalUnsubs) }}</p>
            <p class="text-xs mt-0.5 text-rose-600 dark:text-rose-400">{{ $unsubRate }}%</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 dark:bg-amber-950/30 p-4 shadow-sm">
            <p class="text-xs text-amber-700 dark:text-amber-300">Failed</p>
            <p class="mt-1 text-2xl font-bold text-amber-700 dark:text-amber-300">{{ number_format($totalFailed) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400">Queued</p>
            <p class="mt-1 text-2xl font-bold text-slate-700 dark:text-slate-200">{{ number_format($totalQueued) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400">Total Recipients</p>
            <p class="mt-1 text-2xl font-bold text-slate-700 dark:text-slate-200">{{ number_format($totalSent + $totalFailed + $totalQueued) }}</p>
        </div>
    </div>

    {{-- Delivery bar --}}
    @if($totalSent + $totalFailed + $totalQueued > 0)
    @php
        $total = $totalSent + $totalFailed + $totalQueued;
        $sentPct   = round($totalSent   / $total * 100);
        $failedPct = round($totalFailed / $total * 100);
        $queuedPct = 100 - $sentPct - $failedPct;
    @endphp
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-3">Delivery Progress</h3>
        <div class="flex h-4 rounded-full overflow-hidden w-full">
            @if($sentPct > 0)
                <div class="bg-emerald-500" style="width: {{ $sentPct }}%" title="Sent {{ $sentPct }}%"></div>
            @endif
            @if($failedPct > 0)
                <div class="bg-rose-500" style="width: {{ $failedPct }}%" title="Failed {{ $failedPct }}%"></div>
            @endif
            @if($queuedPct > 0)
                <div class="bg-slate-300 dark:bg-slate-600" style="width: {{ $queuedPct }}%" title="Queued {{ $queuedPct }}%"></div>
            @endif
        </div>
        <div class="flex flex-wrap gap-4 mt-2 text-xs text-slate-500 dark:text-slate-400">
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> Sent {{ $sentPct }}%</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span> Failed {{ $failedPct }}%</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-slate-300 dark:bg-slate-600 inline-block"></span> Queued {{ $queuedPct }}%</span>
        </div>
    </div>
    @endif

    {{-- Engagement bar --}}
    @if($totalSent > 0)
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-3">Engagement Funnel</h3>
        <div class="space-y-3">
            <div>
                <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                    <span>Opened</span><span>{{ number_format($totalOpens) }} / {{ number_format($totalSent) }} &nbsp; {{ $openRate }}%</span>
                </div>
                <div class="h-3 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full" style="width: {{ min($openRate, 100) }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                    <span>Clicked</span><span>{{ number_format($totalClicks) }} / {{ number_format($totalSent) }} &nbsp; {{ $clickRate }}%</span>
                </div>
                <div class="h-3 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 rounded-full" style="width: {{ min($clickRate, 100) }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                    <span>Unsubscribed</span><span>{{ number_format($totalUnsubs) }} / {{ number_format($totalSent) }} &nbsp; {{ $unsubRate }}%</span>
                </div>
                <div class="h-3 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-rose-400 rounded-full" style="width: {{ min($unsubRate, 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Recipients table --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Recipients (Sent)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <th class="py-2 pr-3">Email</th>
                        <th class="py-2 pr-3">Sent At</th>
                        <th class="py-2 pr-3 text-center">Opened</th>
                        <th class="py-2 pr-3 text-center">Clicked</th>
                        <th class="py-2 pr-3 text-center">Unsubscribed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recipients as $row)
                        <tr class="border-b border-slate-100 dark:border-slate-800/70">
                            <td class="py-2 pr-3 text-slate-700 dark:text-slate-200">{{ $row->email }}</td>
                            <td class="py-2 pr-3 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                {{ optional($row->sent_at)->format('Y-m-d H:i') ?? '—' }}
                            </td>
                            <td class="py-2 pr-3 text-center">
                                @if($row->opened_id)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 text-xs font-bold">✓</span>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600">—</span>
                                @endif
                            </td>
                            <td class="py-2 pr-3 text-center">
                                @if($row->clicked_id)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 text-xs font-bold">✓</span>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600">—</span>
                                @endif
                            </td>
                            <td class="py-2 pr-3 text-center">
                                @if($row->unsub_id)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 text-xs font-bold">✓</span>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">No sent recipients found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $recipients->links() }}
        </div>
    </div>

    {{-- Failed deliveries --}}
    @if($failedEmails->count() > 0)
    <div class="rounded-2xl border border-rose-200 dark:border-rose-900/50 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-rose-700 dark:text-rose-300 mb-4">Failed Deliveries ({{ $failedEmails->count() }})</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <th class="py-2 pr-3">Email</th>
                        <th class="py-2 pr-3">Last Attempted</th>
                        <th class="py-2 pr-3">Error</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($failedEmails as $row)
                        <tr class="border-b border-slate-100 dark:border-slate-800/70">
                            <td class="py-2 pr-3 text-slate-700 dark:text-slate-200">{{ $row->email }}</td>
                            <td class="py-2 pr-3 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                {{ optional($row->updated_at)->format('Y-m-d H:i') }}
                            </td>
                            <td class="py-2 pr-3 text-rose-600 dark:text-rose-400 text-xs break-all">
                                {{ $row->last_error ?: '—' }}
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
