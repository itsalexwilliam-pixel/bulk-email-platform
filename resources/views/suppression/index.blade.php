@extends('layouts.app')

@section('page_title', 'Suppression List')

@section('content')
<div class="space-y-6">

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Suppression List</h2>
        <p class="text-sm text-slate-500 mt-0.5">Emails on this list will <strong>never</strong> receive any campaign, drip, or single email — regardless of other settings.</p>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-950/30 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Add single --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm space-y-3">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Add Email</h3>
            <form method="POST" action="{{ route('suppression.store') }}" class="space-y-3">
                @csrf
                <input type="email" name="email" required placeholder="email@example.com"
                       class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                <input type="text" name="reason" placeholder="Reason (optional, e.g. spam)"
                       class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-rose-600 text-white text-sm font-medium hover:bg-rose-700 transition">
                    Add to Suppression List
                </button>
            </form>
        </div>

        {{-- Bulk import --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm space-y-3">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Bulk Import</h3>
            <form method="POST" action="{{ route('suppression.bulk-import') }}" class="space-y-3">
                @csrf
                <textarea name="emails" rows="5" required
                          placeholder="One email per line, or comma-separated:&#10;john@example.com&#10;jane@example.com"
                          class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-rose-500 resize-none"></textarea>
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-slate-800 text-white text-sm font-medium hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 transition">
                    Import All
                </button>
            </form>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Suppressed Emails</h3>
            <span class="text-xs text-slate-400">{{ $entries->total() }} total</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr class="text-left text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Reason</th>
                        <th class="py-3 px-4">Added</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr class="border-t border-slate-100 dark:border-slate-800 hover:bg-slate-50/60 dark:hover:bg-slate-800/20 transition">
                            <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-100">{{ $entry->email }}</td>
                            <td class="py-3 px-4 text-slate-500 dark:text-slate-400">{{ $entry->reason ?: '—' }}</td>
                            <td class="py-3 px-4 text-slate-500 dark:text-slate-400">{{ $entry->created_at->format('Y-m-d') }}</td>
                            <td class="py-3 px-4 text-right">
                                <form method="POST" action="{{ route('suppression.destroy', $entry) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 rounded-lg bg-rose-100 text-rose-700 text-xs font-medium hover:bg-rose-200 transition"
                                            onclick="return confirm('Remove {{ $entry->email }} from suppression list?')">
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-slate-400">
                                <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Suppression list is empty.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($entries->hasPages())
            <div class="border-t border-slate-200 dark:border-slate-800 px-4 py-3">{{ $entries->links() }}</div>
        @endif
    </div>

</div>
@endsection
