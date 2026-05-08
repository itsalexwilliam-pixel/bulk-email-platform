@extends('layouts.app')

@section('page_title', 'Drip Campaigns')

@section('content')
<div class="space-y-6">

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Drip Campaigns</h2>
            <p class="text-sm text-slate-500 mt-0.5">Automated email sequences sent on a schedule to enrolled contacts.</p>
        </div>
        <a href="{{ route('drip.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-teal-600 text-white text-sm font-medium hover:bg-teal-700 transition">
            + New Drip Campaign
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-950/30 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr class="text-left text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Steps</th>
                        <th class="py-3 px-4">Group</th>
                        <th class="py-3 px-4">Enrolled</th>
                        <th class="py-3 px-4">Active</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($drips as $drip)
                        <tr class="border-t border-slate-100 dark:border-slate-800 hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-100">
                                <a href="{{ route('drip.show', $drip) }}" class="hover:text-teal-600 dark:hover:text-teal-400">
                                    {{ $drip->name }}
                                </a>
                            </td>
                            <td class="py-3 px-4">
                                @php
                                    $statusColors = [
                                        'draft'  => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
                                        'active' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                        'paused' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$drip->status] ?? 'bg-slate-100 text-slate-600' }} uppercase">
                                    {{ $drip->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ $drip->steps_count }}</td>
                            <td class="py-3 px-4 text-slate-500 dark:text-slate-400">{{ $drip->group?->name ?? '—' }}</td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ $drip->enrollments_count }}</td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ $drip->active_count }}</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-end gap-2 flex-wrap">
                                    <a href="{{ route('drip.show', $drip) }}"
                                       class="px-3 py-1.5 rounded-lg bg-teal-100 text-teal-700 text-xs font-medium hover:bg-teal-200 transition">
                                        Manage
                                    </a>
                                    @if($drip->status !== 'active')
                                        <form method="POST" action="{{ route('drip.activate', $drip) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-medium hover:bg-emerald-200 transition"
                                                    onclick="return confirm('Activate this drip? Contacts in the linked group will be enrolled.')">
                                                Activate
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('drip.pause', $drip) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg bg-amber-100 text-amber-700 text-xs font-medium hover:bg-amber-200 transition">
                                                Pause
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('drip.destroy', $drip) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-lg bg-rose-100 text-rose-700 text-xs font-medium hover:bg-rose-200 transition"
                                                onclick="return confirm('Delete this drip campaign and all its enrollments?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                <p class="text-sm font-medium">No drip campaigns yet.</p>
                                <a href="{{ route('drip.create') }}" class="mt-2 inline-block text-teal-600 hover:underline text-xs">Create your first drip →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($drips->hasPages())
            <div class="border-t border-slate-200 dark:border-slate-800 px-4 py-3">
                {{ $drips->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
