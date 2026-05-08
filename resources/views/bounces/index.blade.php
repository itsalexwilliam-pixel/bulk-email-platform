@extends('layouts.app')

@section('page_title', 'Bounce Management')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Bounce Management</h2>
        <p class="text-sm text-slate-500 mt-0.5">Contacts with bounced emails are automatically unsubscribed. Hard bounces are permanent delivery failures.</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-rose-200 dark:border-rose-800/50 bg-rose-50/50 dark:bg-rose-950/20 p-4 shadow-sm">
            <p class="text-xs font-medium text-rose-600 dark:text-rose-400 mb-1">Total Bounces</p>
            <p class="text-2xl font-bold text-rose-700 dark:text-rose-300">{{ number_format($totalBounces) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Hard Bounces</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($hardBounces) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Soft Bounces</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($softBounces) }}</p>
        </div>
        <div class="rounded-2xl border border-amber-200 dark:border-amber-800/50 bg-amber-50/50 dark:bg-amber-950/20 p-4 shadow-sm">
            <p class="text-xs font-medium text-amber-600 dark:text-amber-400 mb-1">Complaints</p>
            <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ number_format($complaints) }}</p>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-950/30 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    {{-- Bounced Contacts Table --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Bounced Contacts</h3>
            <span class="text-xs text-slate-400">{{ $bouncedContacts->total() }} contacts</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr class="text-left text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Business</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bouncedContacts as $contact)
                        <tr class="border-t border-slate-100 dark:border-slate-800 hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-100">
                                {{ $contact->name }}
                                <span class="ml-1 px-1.5 py-0.5 text-[10px] rounded bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-300">bounced</span>
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ $contact->email }}</td>
                            <td class="py-3 px-4 text-slate-500 dark:text-slate-400">{{ $contact->business_name ?: '—' }}</td>
                            <td class="py-3 px-4 text-right">
                                <form method="POST" action="{{ route('contacts.clear-bounced', $contact) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                            onclick="return confirm('Clear bounce flag for {{ $contact->email }}? They can receive emails again.')">
                                        Clear Bounce
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto mb-2 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">No bounced contacts!</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bouncedContacts->hasPages())
            <div class="border-t border-slate-200 dark:border-slate-800 px-4 py-3">
                {{ $bouncedContacts->links() }}
            </div>
        @endif
    </div>

    {{-- SES Setup Guide --}}
    <div class="rounded-2xl border border-indigo-200 dark:border-indigo-900/50 bg-indigo-50/70 dark:bg-indigo-950/30 p-5">
        <h3 class="text-sm font-semibold text-indigo-900 dark:text-indigo-200 mb-2">🔧 AWS SES Auto-Bounce Setup</h3>
        <p class="text-xs text-indigo-700 dark:text-indigo-300 mb-3">To automatically handle bounces from AWS SES, configure SNS to send notifications to your webhook:</p>
        <ol class="list-decimal list-inside space-y-1 text-xs text-indigo-700 dark:text-indigo-300">
            <li>Go to AWS SES → <strong>Configuration Sets</strong> → Create or select a configuration set</li>
            <li>Add an <strong>SNS destination</strong> for Bounce and Complaint events</li>
            <li>Create an SNS topic and set the HTTPS endpoint to:<br>
                <code class="mt-1 block bg-indigo-100 dark:bg-indigo-900/50 px-2 py-1 rounded text-indigo-800 dark:text-indigo-200 font-mono select-all">{{ url('/webhooks/ses-bounce') }}</code>
            </li>
            <li>AWS will automatically confirm the subscription and start sending bounce notifications</li>
        </ol>
    </div>

</div>
@endsection
