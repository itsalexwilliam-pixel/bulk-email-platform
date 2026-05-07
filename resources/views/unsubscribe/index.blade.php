@extends('layouts.app')

@section('page_title', 'Unsubscribed Emails')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Unsubscribed Emails</h2>
        <p class="text-sm text-slate-500 mt-1">Contacts who opted out from your campaigns.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr class="text-left text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Contact</th>
                        <th class="py-3 px-4">Unsubscribed At</th>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-8 text-center text-slate-500">No unsubscribed emails found.</td>
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
