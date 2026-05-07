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

            {{-- Name column options --}}
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 space-y-4">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Name Column Mapping <span class="text-slate-400 text-xs font-normal">(use one option)</span></p>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-medium mb-1 text-slate-600 dark:text-slate-400">Option A — Single "name" column</label>
                        <input type="text" name="name_column" value="{{ old('name_column') }}" placeholder="e.g. name"
                               class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <p class="text-xs text-slate-400 pb-1">Leave blank if your CSV has separate first/last name columns.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1 text-slate-600 dark:text-slate-400">Option B — First name column</label>
                        <input type="text" name="first_name_column" value="{{ old('first_name_column', 'first_name') }}" placeholder="e.g. first_name"
                               class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1 text-slate-600 dark:text-slate-400">Option B — Last name column <span class="text-slate-400 font-normal">(optional)</span></label>
                        <input type="text" name="last_name_column" value="{{ old('last_name_column', 'last_name') }}" placeholder="e.g. last_name"
                               class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Email Column Header</label>
                <input type="text" name="email_column" value="{{ old('email_column', 'email') }}" required
                       class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Business Name Column Header <span class="text-slate-400 text-xs font-normal">(optional)</span></label>
                    <input type="text" name="business_name_column" value="{{ old('business_name_column', 'company') }}"
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Website Column Header <span class="text-slate-400 text-xs font-normal">(optional)</span></label>
                    <input type="text" name="website_column" value="{{ old('website_column', 'website') }}"
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="rounded-xl border border-indigo-200 dark:border-indigo-900/50 bg-indigo-50/70 dark:bg-indigo-950/30 px-4 py-3 text-sm text-indigo-800 dark:text-indigo-200">
                Contacts will be added to selected <span class="font-semibold">Lists (Groups)</span>.
                <div class="mt-1 text-xs space-y-0.5">
                    <div>CSV format example (single name):
                        <code class="px-1.5 py-0.5 rounded bg-white/70 dark:bg-slate-900/70">name,email,company,website</code>
                    </div>
                    <div>CSV format example (split name):
                        <code class="px-1.5 py-0.5 rounded bg-white/70 dark:bg-slate-900/70">first_name,last_name,email,company,website</code>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Assign to Lists (Groups) <span class="text-slate-400 text-xs font-normal">(optional)</span></label>
                <select id="groupsSelect" name="groups[]" multiple size="8"
                        class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
                <p id="groupSelectionHint" class="mt-1 text-xs text-slate-500">Hold Ctrl/Cmd to select multiple groups (optional).</p>
            </div>

            <div class="flex items-center gap-3">
                <button id="importSubmitBtn" type="submit" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
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
    const groupSelectionHint = document.getElementById('groupSelectionHint');

    function refreshGroupHint() {
        const selectedCount = groupsSelect ? Array.from(groupsSelect.selectedOptions).length : 0;
        if (groupSelectionHint) {
            groupSelectionHint.textContent = selectedCount > 0
                ? `Selected Lists (Groups): ${selectedCount}`
                : 'Hold Ctrl/Cmd to select multiple groups (optional).';
            groupSelectionHint.classList.toggle('text-emerald-600', selectedCount > 0);
            groupSelectionHint.classList.toggle('text-slate-500', selectedCount === 0);
        }
    }

    groupsSelect?.addEventListener('change', refreshGroupHint);
</script>
@endpush
