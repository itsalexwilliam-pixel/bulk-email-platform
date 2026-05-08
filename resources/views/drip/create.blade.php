@extends('layouts.app')

@section('page_title', 'New Drip Campaign')

@section('content')
<div class="max-w-2xl space-y-6">

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Create Drip Campaign</h2>
        <p class="text-sm text-slate-500 mt-0.5">Set a name, link a contact group, then add email steps.</p>
    </div>

    <form action="{{ route('drip.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm space-y-4">

            <div>
                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Drip Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                       placeholder="e.g. Welcome Sequence, Post-Purchase Follow-up">
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Description (optional)</label>
                <textarea name="description" rows="2"
                          class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none"
                          placeholder="Internal notes about this drip sequence…">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Auto-enroll from Group (optional)</label>
                <select name="group_id"
                        class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">— No auto-enroll —</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" @selected(old('group_id') == $group->id)>
                            {{ $group->name }} ({{ $group->contacts()->count() }} contacts)
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">When you activate this drip, all contacts in the selected group will be automatically enrolled.</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-teal-600 text-white text-sm font-medium hover:bg-teal-700 transition">
                Create & Add Steps
            </button>
            <a href="{{ route('drip.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
