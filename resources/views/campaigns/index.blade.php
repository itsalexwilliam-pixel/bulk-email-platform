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
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('campaigns.edit', $campaign) }}"
                                       class="px-3 py-1.5 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-medium hover:bg-indigo-200 transition">
                                        Edit
                                    </a>
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
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">No campaigns found.</td>
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
@endsection
