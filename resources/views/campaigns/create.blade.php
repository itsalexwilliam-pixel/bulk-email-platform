@extends('layouts.app')

@section('page_title', 'Campaign Builder')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-sm font-medium text-indigo-600 dark:text-indigo-300">
                <span class="w-7 h-7 rounded-full bg-indigo-600 text-white grid place-items-center text-xs">1</span>
                Select Audience
            </div>
            <div class="h-px w-6 bg-slate-300 dark:bg-slate-700"></div>
            <div class="flex items-center gap-2 text-sm font-medium text-slate-500 dark:text-slate-400">
                <span class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-700 grid place-items-center text-xs">2</span>
                Campaign Details
            </div>
            <div class="h-px w-6 bg-slate-300 dark:bg-slate-700"></div>
            <div class="flex items-center gap-2 text-sm font-medium text-slate-500 dark:text-slate-400">
                <span class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-700 grid place-items-center text-xs">3</span>
                Email Editor
            </div>
            <div class="h-px w-6 bg-slate-300 dark:bg-slate-700"></div>
            <div class="flex items-center gap-2 text-sm font-medium text-slate-500 dark:text-slate-400">
                <span class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-700 grid place-items-center text-xs">4</span>
                Review & Send
            </div>
        </div>
    </div>

    <form action="{{ route('campaigns.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm space-y-5">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Step 1 & 2: Audience + Details</h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Campaign Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Subject</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Schedule (optional)</label>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                       class="w-full md:w-80 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-slate-500">If set, campaign status becomes scheduled; otherwise saved as draft.</p>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-indigo-200 dark:border-indigo-900/50 bg-indigo-50/70 dark:bg-indigo-950/30 p-4">
                    <label class="block text-sm font-semibold mb-1 text-indigo-900 dark:text-indigo-200">Select Lists (Groups) <span class="text-rose-600">*</span></label>
                    <select id="groupIdsSelect" name="group_ids[]" multiple size="8" required
                            class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" @selected(collect(old('group_ids'))->contains($group->id)) data-count="{{ $group->contacts()->count() }}">
                                {{ $group->name }} ({{ $group->contacts()->count() }} contacts)
                            </option>
                        @endforeach
                    </select>
                    <p id="groupSelectionSummary" class="mt-2 text-xs text-rose-600">Please select at least one List (Group).</p>
                    <p class="mt-1 text-xs text-indigo-800 dark:text-indigo-300">Recipients are resolved from selected lists. Duplicates are automatically removed.</p>
                </div>

                <details class="rounded-xl border border-slate-200 dark:border-slate-700 p-3">
                    <summary class="cursor-pointer text-sm font-medium text-slate-700 dark:text-slate-300">Advanced (optional): Add manual contacts</summary>
                    <div class="mt-3">
                        <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Select Contacts (optional)</label>
                        <select name="contact_ids[]" multiple size="8"
                                class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach($contacts as $contact)
                                <option value="{{ $contact->id }}" @selected(collect(old('contact_ids'))->contains($contact->id))>
                                    {{ $contact->name }} ({{ $contact->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </details>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm space-y-4">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Step 3: Email Editor</h2>
            <textarea name="body" id="body-editor" rows="12" required>{{ old('body') }}</textarea>

            <button type="button" id="previewBtn"
                    class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                Preview
            </button>

            <div id="previewWrapper" class="hidden rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <h4 class="text-sm font-semibold mb-2 text-slate-700 dark:text-slate-200">Preview</h4>
                <div id="previewBody" class="prose dark:prose-invert max-w-none text-sm"></div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4">Step 4: Review & Send</h2>
            <div class="flex flex-wrap items-center gap-3">
                <button id="saveCampaignBtn" type="submit" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Save Campaign
                </button>
                <a href="{{ route('campaigns.index') }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const bodyEditor = document.getElementById('body-editor');
    bodyEditor?.classList.add(
        'w-full',
        'rounded-xl',
        'border',
        'border-slate-300',
        'dark:border-slate-700',
        'bg-white',
        'dark:bg-slate-950',
        'px-4',
        'py-3',
        'text-sm',
        'focus:outline-none',
        'focus:ring-2',
        'focus:ring-indigo-500'
    );

    const previewBtn = document.getElementById('previewBtn');
    const previewWrapper = document.getElementById('previewWrapper');
    const previewBody = document.getElementById('previewBody');
    const groupIdsSelect = document.getElementById('groupIdsSelect');
    const saveCampaignBtn = document.getElementById('saveCampaignBtn');
    const groupSelectionSummary = document.getElementById('groupSelectionSummary');

    previewBtn?.addEventListener('click', function () {
        const raw = bodyEditor ? bodyEditor.value : '';
        previewBody.innerHTML = raw;
        previewWrapper.classList.remove('hidden');
    });

    function updateGroupSelectionState() {
        const selected = groupIdsSelect ? Array.from(groupIdsSelect.selectedOptions) : [];
        const selectedCount = selected.length;
        const estimatedRecipients = selected.reduce((sum, opt) => sum + Number(opt.dataset.count || 0), 0);

        if (saveCampaignBtn) {
            saveCampaignBtn.disabled = selectedCount === 0;
        }

        if (groupSelectionSummary) {
            if (selectedCount === 0) {
                groupSelectionSummary.textContent = 'Please select at least one List (Group).';
                groupSelectionSummary.classList.remove('text-emerald-600');
                groupSelectionSummary.classList.add('text-rose-600');
            } else {
                groupSelectionSummary.textContent = `Selected Lists (Groups): ${selectedCount} | Estimated recipients: ${estimatedRecipients}`;
                groupSelectionSummary.classList.remove('text-rose-600');
                groupSelectionSummary.classList.add('text-emerald-600');
            }
        }
    }

    groupIdsSelect?.addEventListener('change', updateGroupSelectionState);
    updateGroupSelectionState();
</script>
@endpush
