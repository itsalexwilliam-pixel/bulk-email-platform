@extends('layouts.app')

@section('page_title', 'Import Contacts')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Import Contacts from CSV</h2>
        <p class="text-sm text-slate-500 mt-1">Map columns and bulk import validated contacts.</p>
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

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <form action="{{ route('import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">CSV File</label>
                <input type="file" name="csv_file" accept=".csv,.txt" required
                       class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Name Column Header</label>
                    <input type="text" name="name_column" value="{{ old('name_column', 'name') }}" required
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Email Column Header</label>
                    <input type="text" name="email_column" value="{{ old('email_column', 'email') }}" required
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="rounded-xl border border-indigo-200 dark:border-indigo-900/50 bg-indigo-50/70 dark:bg-indigo-950/30 px-4 py-3 text-sm text-indigo-800 dark:text-indigo-200">
                Contacts will be added to selected <span class="font-semibold">Lists (Groups)</span>.
                <div class="mt-1 text-xs">
                    CSV format example:
                    <code class="px-1.5 py-0.5 rounded bg-white/70 dark:bg-slate-900/70">name,email</code>
                    <code class="px-1.5 py-0.5 rounded bg-white/70 dark:bg-slate-900/70">John Doe,john@example.com</code>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Assign to Lists (Groups) <span class="text-rose-600">*</span></label>
                <select id="groupsSelect" name="groups[]" multiple size="8" required
                        class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
                <p id="groupSelectionHint" class="mt-1 text-xs text-rose-600">Please select at least one List (Group) to enable import.</p>
            </div>

            <div class="flex items-center gap-3">
                <button id="importSubmitBtn" type="submit" disabled class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Import
                </button>
                <a href="{{ route('contacts.index') }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const groupsSelect = document.getElementById('groupsSelect');
    const importSubmitBtn = document.getElementById('importSubmitBtn');
    const groupSelectionHint = document.getElementById('groupSelectionHint');

    function refreshImportButtonState() {
        const selectedCount = groupsSelect ? Array.from(groupsSelect.selectedOptions).length : 0;
        const enabled = selectedCount > 0;

        if (importSubmitBtn) {
            importSubmitBtn.disabled = !enabled;
        }

        if (groupSelectionHint) {
            groupSelectionHint.textContent = enabled
                ? `Selected Lists (Groups): ${selectedCount}`
                : 'Please select at least one List (Group) to enable import.';
            groupSelectionHint.classList.toggle('text-rose-600', !enabled);
            groupSelectionHint.classList.toggle('text-emerald-600', enabled);
        }
    }

    groupsSelect?.addEventListener('change', refreshImportButtonState);
    refreshImportButtonState();
</script>
@endpush
