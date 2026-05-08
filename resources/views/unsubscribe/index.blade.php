@extends('layouts.app')

@section('page_title', 'Unsubscribed Emails')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Unsubscribed Emails</h2>
        <p class="text-sm text-slate-500 mt-1">Contacts who opted out from your campaigns.</p>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr class="text-left text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Contact</th>
                        <th class="py-3 px-4">Unsubscribed At</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unsubscribes as $item)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="py-3 px-4 text-slate-800 dark:text-slate-100">{{ $item->email }}</td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                                @if($item->contact)
                                    {{ $item->contact->name }} ({{ $item->contact->email }})
                                @else
                                    <span class="text-slate-400">N/A</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ optional($item->unsubscribed_at)->format('Y-m-d H:i:s') }}</td>
                            <td class="py-3 px-4 text-right">
                                <form method="POST" action="{{ route('unsubscribes.destroy', $item) }}"
                                      onsubmit="return confirm('Remove {{ $item->email }} from the unsubscribe list? They will be able to receive emails again.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rose-200 dark:border-rose-800/50 text-rose-600 dark:text-rose-400 text-xs font-medium hover:bg-rose-50 dark:hover:bg-rose-900/20 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500">No unsubscribed emails found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($unsubscribes, 'links'))
            <div class="border-t border-slate-200 dark:border-slate-800 px-4 py-3">
                {{ $unsubscribes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
