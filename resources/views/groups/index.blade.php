@extends('layouts.app')

@section('page_title', 'Groups')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Groups</h2>
        <p class="text-sm text-slate-500 mt-1">Create and manage your contact lists.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-700 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <div class="xl:col-span-4">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-4">Create Group</h3>
                <form method="POST" action="{{ route('groups.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Group Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                        Create
                    </button>
                </form>
            </div>
        </div>

        <div class="xl:col-span-8">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-4">All Groups</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                <th class="py-3 pr-4">Name</th>
                                <th class="py-3 pr-4">Contacts</th>
                                <th class="py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($groups as $group)
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="py-3 pr-4 font-medium text-slate-900 dark:text-slate-100">{{ $group->name }}</td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ $group->contacts_count }}</td>
                                    <td class="py-3">
                                        <form method="POST" action="{{ route('groups.destroy', $group) }}" onsubmit="return confirm('Delete this group?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-rose-600 text-white text-xs font-medium hover:bg-rose-700 transition">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-slate-500">No groups found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $groups->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
