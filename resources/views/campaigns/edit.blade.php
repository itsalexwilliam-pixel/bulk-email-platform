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
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email Body</label>
                    <button type="button" id="loadTemplateBtn"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-indigo-300 dark:border-indigo-700 text-indigo-600 dark:text-indigo-300 text-xs font-medium hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Load Template
                    </button>
                </div>

                {{-- Mode toggle tabs --}}
                <div class="flex gap-1 mb-2">
                    <button type="button" id="tabRichText"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-600 text-white transition">
                        Rich Text
                    </button>
                    <button type="button" id="tabHtml"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        HTML
                    </button>
                </div>

                {{-- Hidden textarea submitted with form (no required — validated in JS) --}}
                <textarea name="body" id="body-editor" class="sr-only">{{ old('body', $campaign->body) }}</textarea>

                {{-- Quill rich-text editor --}}
                <div id="quill-editor" style="height:420px;" class="rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950"></div>

                {{-- Raw HTML textarea --}}
                <textarea id="html-editor" style="height:420px;" class="hidden w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-3 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" placeholder="Paste or type raw HTML here…"></textarea>

                @error('body')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
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
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Preview Email
            </button>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Schedule (optional)</label>
                    <input type="datetime-local" name="scheduled_at"
                           value="{{ old('scheduled_at', $campaign->scheduled_at ? \Illuminate\Support\Carbon::parse($campaign->scheduled_at)->format('Y-m-d\TH:i') : '') }}"
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-slate-500">If set, campaign status becomes scheduled; otherwise saved as draft.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Send Speed (emails/min)</label>
                    <input type="number" min="1" max="10000" step="1" name="emails_per_minute"
                           value="{{ old('emails_per_minute', $campaign->emails_per_minute) }}"
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

            <div class="rounded-xl border border-cyan-200 dark:border-cyan-900/50 bg-cyan-50/70 dark:bg-cyan-950/30 p-4">
                <h4 class="text-sm font-semibold text-cyan-900 dark:text-cyan-200">Selected Group Members (preview)</h4>
                <p class="mt-1 text-xs text-cyan-800 dark:text-cyan-300">
                    Niche selected groups ke contacts ka preview hai. Final recipients group + manual contacts merge karke deduplicate hote hain.
                </p>
                <div id="groupMembersPreview" class="mt-3 rounded-lg border border-cyan-200 dark:border-cyan-800 bg-white/80 dark:bg-slate-900/60 p-3 text-xs text-slate-700 dark:text-slate-200 max-h-48 overflow-auto">
                    Select one or more groups to see member preview.
                </div>
            </div>

            <details class="rounded-xl border border-slate-200 dark:border-slate-700 p-3">
                <summary class="cursor-pointer text-sm font-medium text-slate-700 dark:text-slate-300">Advanced (optional): Add manual contacts</summary>
                <div class="mt-3">
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">All Account Contacts (optional manual add)</label>
                    <p class="mb-2 text-xs text-slate-500 dark:text-slate-400">
                        Ye group members list nahi hai. Is list se aap extra contacts manually add kar sakte ho.
                    </p>
                    <select id="manualContactIdsSelect" name="contact_ids[]" multiple size="8"
                            class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($contacts as $contact)
                            <option value="{{ $contact->id }}" @selected(collect(old('contact_ids', $selectedContactIds ?? []))->contains($contact->id))
                                    data-contact-name="{{ $contact->name }}"
                                    data-contact-email="{{ $contact->email }}"
                                    data-group-ids="{{ $contact->groups->pluck('id')->implode(',') }}">
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

        </div>
    </form>
</div>

{{-- Preview Modal --}}
<div id="previewModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-800">
            <h3 class="font-semibold text-slate-900 dark:text-white">Email Preview</h3>
            <button type="button" id="closePreviewBtn"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex-1 overflow-auto p-4">
            <iframe id="previewFrame" class="w-full h-[65vh] rounded-xl border border-slate-200 dark:border-slate-700"></iframe>
        </div>
    </div>
</div>
@endsection

@php
    $contactGroupData = $groupContacts->map(function ($contact) {
        return [
            'name' => $contact->name,
            'email' => $contact->email,
            'group_ids' => $contact->groups->pluck('id')->map(function ($id) {
                return (string) $id;
            })->values()->all(),
        ];
    });
@endphp

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
    // ── Quill initialisation ──────────────────────────────────────────────────
    const quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, 4, 5, 6, false] }],
                [{ font: [] }],
                [{ size: ['small', false, 'large', 'huge'] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ align: [] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ indent: '-1' }, { indent: '+1' }],
                ['link', 'image', 'video'],
                ['blockquote', 'code-block'],
                ['clean'],
            ],
        },
    });

    const bodyTextarea = document.getElementById('body-editor');
    const htmlEditor   = document.getElementById('html-editor');
    const quillEditor  = document.getElementById('quill-editor');
    const tabRichText  = document.getElementById('tabRichText');
    const tabHtml      = document.getElementById('tabHtml');

    // Pre-fill from existing campaign body
    const existingContent = bodyTextarea.value.trim();
    if (existingContent) {
        quill.clipboard.dangerouslyPasteHTML(existingContent);
        htmlEditor.value = existingContent;
    }

    // Keep hidden textarea in sync with Quill on every content change
    quill.on('text-change', function () {
        if (htmlEditor.classList.contains('hidden')) {
            bodyTextarea.value = quill.root.innerHTML;
        }
    });

    // ── Tab switching ─────────────────────────────────────────────────────────
    const activeTab   = 'bg-indigo-600 text-white';
    const inactiveTab = 'border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800';

    tabRichText?.addEventListener('click', function () {
        quill.clipboard.dangerouslyPasteHTML(htmlEditor.value || '');
        quillEditor.classList.remove('hidden');
        htmlEditor.classList.add('hidden');
        tabRichText.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${activeTab}`;
        tabHtml.className     = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${inactiveTab}`;
    });

    tabHtml?.addEventListener('click', function () {
        htmlEditor.value = quill.root.innerHTML;
        bodyTextarea.value = htmlEditor.value;
        htmlEditor.classList.remove('hidden');
        quillEditor.classList.add('hidden');
        tabHtml.className     = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${activeTab}`;
        tabRichText.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${inactiveTab}`;
    });

    // Keep hidden textarea in sync when user edits the raw HTML textarea
    htmlEditor?.addEventListener('input', function () {
        if (quillEditor.classList.contains('hidden')) {
            bodyTextarea.value = htmlEditor.value;
        }
    });

    // Sync active editor → hidden textarea before form submit, then validate
    document.querySelector('form[method="POST"]').addEventListener('submit', function (e) {
        const content = htmlEditor.classList.contains('hidden')
            ? quill.root.innerHTML
            : htmlEditor.value;
        bodyTextarea.value = content;

        const plainText = content.replace(/<[^>]*>/g, '').trim();
        if (!plainText) {
            e.preventDefault();
            if (!document.getElementById('body-error')) {
                const err = document.createElement('p');
                err.id = 'body-error';
                err.className = 'mt-1 text-xs text-rose-600';
                err.textContent = 'Please fill in the email body.';
                bodyTextarea.parentNode.insertBefore(err, bodyTextarea.nextSibling);
            }
        } else {
            const existing = document.getElementById('body-error');
            if (existing) existing.remove();
        }
    });

    // ── Preview modal ─────────────────────────────────────────────────────────
    const previewModal  = document.getElementById('previewModal');
    const previewFrame  = document.getElementById('previewFrame');
    const previewBtn = document.getElementById('previewBtn');
    const groupIdsSelect = document.getElementById('groupIdsSelect');
    const updateCampaignBtn = document.getElementById('updateCampaignBtn');
    const groupSelectionSummary = document.getElementById('groupSelectionSummary');
    const groupMembersPreview = document.getElementById('groupMembersPreview');
    const manualContactIdsSelect = document.getElementById('manualContactIdsSelect');
    const contactGroupData = @json($contactGroupData);

    previewBtn?.addEventListener('click', function () {
        const html = htmlEditor.classList.contains('hidden') ? quill.root.innerHTML : htmlEditor.value;
        const doc = previewFrame?.contentWindow?.document;
        if (!doc) return;
        doc.open();
        doc.write(`<!DOCTYPE html><html><body style="font-family:sans-serif;padding:16px">${html}</body></html>`);
        doc.close();
        previewModal?.classList.remove('hidden');
        previewModal?.classList.add('flex');
    });

    document.getElementById('closePreviewBtn')?.addEventListener('click', function () {
        previewModal?.classList.add('hidden');
        previewModal?.classList.remove('flex');
    });
    previewModal?.addEventListener('click', function (e) {
        if (e.target === previewModal) {
            previewModal.classList.add('hidden');
            previewModal.classList.remove('flex');
        }
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

        updateGroupMembersPreview();
    }

    function updateGroupMembersPreview() {
        if (!groupMembersPreview || !groupIdsSelect) {
            return;
        }

        const selectedGroupIds = new Set(
            Array.from(groupIdsSelect.selectedOptions).map((opt) => String(opt.value))
        );

        if (selectedGroupIds.size === 0) {
            groupMembersPreview.textContent = 'Select one or more groups to see member preview.';
            return;
        }

        const memberLines = [];
        contactGroupData.forEach((contact) => {
            const isInSelectedGroup = contact.group_ids.some((gid) => selectedGroupIds.has(String(gid)));
            if (isInSelectedGroup) {
                memberLines.push(`${contact.name} (${contact.email})`);
            }
        });

        if (memberLines.length === 0) {
            groupMembersPreview.textContent = 'No contacts found in selected groups.';
            return;
        }

        groupMembersPreview.innerHTML = memberLines
            .map((line) => `<div class="py-0.5">${line}</div>`)
            .join('');
    }

    groupIdsSelect?.addEventListener('change', updateGroupSelectionState);
    groupIdsSelect?.addEventListener('input', updateGroupSelectionState);
    manualContactIdsSelect?.addEventListener('change', updateGroupMembersPreview);
    updateGroupSelectionState();

    // ── Load Template modal ───────────────────────────────────────────────────
    const loadTemplateBtn = document.getElementById('loadTemplateBtn');
    const tplModal        = document.getElementById('tplModal');
    const tplModalClose   = document.getElementById('tplModalClose');
    const tplSearch       = document.getElementById('tplSearch');
    const tplList         = document.getElementById('tplList');

    loadTemplateBtn?.addEventListener('click', () => {
        tplModal?.classList.remove('hidden');
        tplModal?.classList.add('flex');
        tplSearch?.focus();
    });
    function closeTplModal() {
        tplModal?.classList.add('hidden');
        tplModal?.classList.remove('flex');
    }
    tplModalClose?.addEventListener('click', closeTplModal);
    tplModal?.addEventListener('click', (e) => { if (e.target === tplModal) closeTplModal(); });

    tplSearch?.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        tplList?.querySelectorAll('[data-tpl-item]').forEach(item => {
            item.style.display = item.dataset.name.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    tplList?.querySelectorAll('[data-tpl-item]').forEach(item => {
        item.addEventListener('click', function () {
            const id = this.dataset.id;
            fetch(`/templates/${id}/load`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(tpl => {
                quill.clipboard.dangerouslyPasteHTML(tpl.body || '');
                bodyTextarea.value = tpl.body || '';
                htmlEditor.value   = tpl.body || '';
                const subjectInput = document.querySelector('input[name="subject"]');
                if (subjectInput && tpl.subject && confirm('Replace subject with template subject?')) {
                    subjectInput.value = tpl.subject;
                }
                closeTplModal();
            })
            .catch(() => alert('Failed to load template.'));
        });
    });
</script>

{{-- Template Picker Modal --}}
<div id="tplModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-lg max-h-[80vh] flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-800">
            <h3 class="font-semibold text-slate-900 dark:text-white">Load Template</h3>
            <button type="button" id="tplModalClose"
                    class="h-8 w-8 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-5 pt-4 pb-2">
            <input type="text" id="tplSearch" placeholder="Search templates…"
                   class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div id="tplList" class="flex-1 overflow-y-auto px-5 pb-5 space-y-2 mt-2">
            @php
                $accountId = (int)(auth()->user()?->account_id ?? 0);
                $availableTemplates = \App\Models\EmailTemplate::where('account_id', $accountId)->orderBy('name')->get();
            @endphp
            @forelse($availableTemplates as $tpl)
                <div data-tpl-item data-id="{{ $tpl->id }}" data-name="{{ $tpl->name }}"
                     class="rounded-xl border border-slate-200 dark:border-slate-700 p-3 cursor-pointer hover:border-indigo-400 dark:hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10 transition">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-medium text-sm text-slate-900 dark:text-white">{{ $tpl->name }}</span>
                        @if($tpl->category)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500">{{ $tpl->category }}</span>
                        @endif
                    </div>
                    @if($tpl->subject)
                        <p class="text-xs text-slate-400 mt-0.5">{{ $tpl->subject }}</p>
                    @endif
                </div>
            @empty
                <div class="text-center py-8 text-slate-400 text-sm">
                    No templates yet.
                    <a href="{{ route('templates.create') }}" class="text-indigo-600 hover:underline" target="_blank">Create one →</a>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endpush
