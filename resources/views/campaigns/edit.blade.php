@extends('layouts.app')

@section('page_title', 'Edit Campaign')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Edit Campaign</h2>
                <p class="text-sm text-slate-500 mt-1">Update audience, content, and sending options.</p>
            </div>
            <form action="{{ route('campaigns.send-test-email', $campaign) }}" method="POST" class="flex items-center gap-2">
                @csrf
                <input type="email" name="test_email" required placeholder="test@example.com"
                       class="w-52 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-xl bg-cyan-600 text-white text-sm font-medium hover:bg-cyan-700 transition">
                    Send Test Campaign Mail
                </button>
            </form>
        </div>
    </div>

    <form action="{{ route('campaigns.update', $campaign) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm space-y-5">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">Campaign Details</h3>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Campaign Name</label>
                    <input type="text" name="name" value="{{ old('name', $campaign->name) }}" required
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Subject</label>
                    <input type="text" name="subject" value="{{ old('subject', $campaign->subject) }}" required
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Email Body</label>
                <textarea name="body" id="body-editor" rows="12" required>{{ old('body', $campaign->body) }}</textarea>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Attachment (optional, max 10MB)</label>
                    <input type="file" name="attachment"
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('attachment')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                @if($campaign->attachment_path)
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-3 text-sm">
                        <p class="text-slate-700 dark:text-slate-300">
                            Current attachment: <span class="font-medium">{{ $campaign->attachment_name ?: basename($campaign->attachment_path) }}</span>
                        </p>
                        <label class="mt-2 inline-flex items-center gap-2 text-rose-600">
                            <input type="checkbox" name="remove_attachment" value="1" class="rounded border-slate-300 dark:border-slate-700">
                            Remove current attachment
                        </label>
                    </div>
                @endif
            </div>

            <button type="button" id="previewBtn"
                    class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                Preview
            </button>

            <div>
                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Schedule (optional)</label>
                <input type="datetime-local" name="scheduled_at"
                       value="{{ old('scheduled_at', $campaign->scheduled_at ? \Illuminate\Support\Carbon::parse($campaign->scheduled_at)->format('Y-m-d\TH:i') : '') }}"
                       class="w-full md:w-80 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-slate-500">If set, campaign status becomes scheduled; otherwise saved as draft.</p>
            </div>

            <div class="rounded-xl border border-amber-200 dark:border-amber-900/50 bg-amber-50/70 dark:bg-amber-950/30 p-4 space-y-3">
                <label class="inline-flex items-center gap-2 text-sm font-semibold text-amber-900 dark:text-amber-200">
                    <input type="checkbox" name="warmup_enabled" value="1" @checked(old('warmup_enabled', $campaign->warmup_enabled))
                           class="rounded border-slate-300 dark:border-slate-700">
                    Enable Warmup (Domain/IP)
                </label>
                <p class="text-xs text-amber-800 dark:text-amber-300">Warmup is applied only when enabled. History is preserved when disabled.</p>
                <p class="text-xs text-slate-600 dark:text-slate-300">
                    Current warmup day: <span class="font-semibold">Day {{ $campaign->getEffectiveWarmupDay() }}</span>
                    @if($campaign->warmup_started_at)
                        | Started: {{ $campaign->warmup_started_at->format('Y-m-d H:i') }}
                    @endif
                </p>

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
                                <tr @class(['font-semibold text-amber-700 dark:text-amber-300' => $day === $campaign->getEffectiveWarmupDay()])>
                                    <td class="py-1 pr-4">Day {{ $day }}</td>
                                    <td class="py-1">{{ $cap }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border border-indigo-200 dark:border-indigo-900/50 bg-indigo-50/70 dark:bg-indigo-950/30 p-4">
                <label class="block text-sm font-semibold mb-1 text-indigo-900 dark:text-indigo-200">Select Lists (Groups) <span class="text-rose-600">*</span></label>
                <select id="groupIdsSelect" name="group_ids[]" multiple size="8" required
                        class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}"
                                @selected(collect(old('group_ids', $selectedGroupIds ?? []))->contains($group->id))
                                data-count="{{ $group->contacts()->count() }}">
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
                            <option value="{{ $contact->id }}" @selected(collect(old('contact_ids', $selectedContactIds ?? []))->contains($contact->id))>
                                {{ $contact->name }} ({{ $contact->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </details>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <div class="flex flex-wrap items-center gap-3">
                <button id="updateCampaignBtn" type="submit"
                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Update Campaign
                </button>
                <a href="{{ route('campaigns.index') }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Cancel
                </a>
            </div>

            <div id="previewWrapper" class="hidden mt-4 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <h4 class="text-sm font-semibold mb-2 text-slate-700 dark:text-slate-200">Email Preview</h4>
                <div id="previewBody" class="prose dark:prose-invert max-w-none text-sm"></div>
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
    const updateCampaignBtn = document.getElementById('updateCampaignBtn');
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

        if (updateCampaignBtn) {
            updateCampaignBtn.disabled = selectedCount === 0;
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
