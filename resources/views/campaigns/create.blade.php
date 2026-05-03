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

    <form action="{{ route('campaigns.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Schedule (optional)</label>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-slate-500">If set, campaign status becomes scheduled; otherwise saved as draft.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Send Speed (emails/min)</label>
                    <input type="number" min="1" max="10000" step="1" name="emails_per_minute" value="{{ old('emails_per_minute') }}"
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="e.g. 60">
                    <p class="mt-1 text-xs text-slate-500">Leave blank for default worker throughput. Example: 60 = approx 60 emails per minute.</p>
                    @error('emails_per_minute')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="rounded-xl border border-amber-200 dark:border-amber-900/50 bg-amber-50/70 dark:bg-amber-950/30 p-4 space-y-3">
                <label class="inline-flex items-center gap-2 text-sm font-semibold text-amber-900 dark:text-amber-200">
                    <input type="checkbox" name="warmup_enabled" value="1" @checked(old('warmup_enabled'))
                           class="rounded border-slate-300 dark:border-slate-700">
                    Enable Warmup (Domain/IP)
                </label>
                <p class="text-xs text-amber-800 dark:text-amber-300">When enabled, sending cap is auto-limited by warmup day and increases gradually.</p>

                <div class="overflow-x-auto">
                    <table class="min-w-[320px] text-xs">
                        <thead>
                            <tr class="text-left text-slate-600 dark:text-slate-300">
                                <th class="py-1 pr-4">Day</th>
                                <th class="py-1">Emails</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-700 dark:text-slate-200">
                            @foreach($warmupSchedule as $day => $cap)
                                <tr>
                                    <td class="py-1 pr-4">Day {{ $day }}</td>
                                    <td class="py-1">{{ $cap }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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

            <div>
                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Attachment (optional, max 10MB)</label>
                <input type="file" name="attachment"
                       class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('attachment')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

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

        const escapeHtml = (value) =>
            value
                .replace(/&/g, '&amp;')
                .replace(/</g, '<')
                .replace(/>/g, '>')
                .replace(/"/g, '"')
                .replace(/'/g, '&#039;');

        const hasHtmlTags = /<\/?[a-z][\s\S]*>/i.test(raw);

        if (hasHtmlTags) {
            previewBody.innerHTML = raw;
            previewBody.classList.remove('whitespace-pre-wrap');
        } else {
            previewBody.innerHTML = escapeHtml(raw).replace(/\r?\n/g, '<br>');
            previewBody.classList.add('whitespace-pre-wrap');
        }

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
