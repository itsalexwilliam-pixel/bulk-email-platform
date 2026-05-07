@extends('layouts.app')

@section('page_title', 'Send Single Email')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Send Single Email</h2>
                <p class="text-sm text-slate-500 mt-1">Send a one-off tracked email immediately using a selected SMTP server.</p>
            </div>
            <a href="{{ route('reports.single-email') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                View Email Reports
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->has('single_email'))
        <div class="rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-300 px-4 py-3 text-sm">
            {{ $errors->first('single_email') }}
        </div>
    @endif

    <form method="POST" action="{{ route('single-email.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        {{-- Section 1: Recipients & Server --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm space-y-4">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">Recipients & Server</h3>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">To (Email) <span class="text-rose-600">*</span></label>
                    <input type="email" name="to" value="{{ old('to') }}" required
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="recipient@example.com">
                    @error('to')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">SMTP Server <span class="text-rose-600">*</span></label>
                    <select name="smtp_server_id" required
                            class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select SMTP server…</option>
                        @foreach ($smtpServers as $server)
                            <option value="{{ $server->id }}" @selected((string) old('smtp_server_id') === (string) $server->id)>
                                {{ $server->name }} ({{ $server->host }}:{{ $server->port }})
                            </option>
                        @endforeach
                    </select>
                    @error('smtp_server_id')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">CC <span class="text-xs text-slate-400">(optional, comma separated)</span></label>
                    <input type="text" name="cc" value="{{ old('cc') }}"
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="cc1@example.com, cc2@example.com">
                    @error('cc')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">BCC <span class="text-xs text-slate-400">(optional, comma separated)</span></label>
                    <input type="text" name="bcc" value="{{ old('bcc') }}"
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="bcc1@example.com, bcc2@example.com">
                    @error('bcc')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Section 2: From Override --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm space-y-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">From Override <span class="text-xs font-normal text-slate-400">(optional)</span></h3>
                <p class="text-xs text-slate-500 mt-0.5">Leave blank to use the SMTP server's default sender.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">From Name</label>
                    <input type="text" name="from_name" value="{{ old('from_name') }}"
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="e.g. John from Acme">
                    @error('from_name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">From Email</label>
                    <input type="text" name="from_email" value="{{ old('from_email') }}"
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="sender@yourdomain.com">
                    @error('from_email')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Section 3: Email Content --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm space-y-4">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">Email Content</h3>

            <div>
                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Subject <span class="text-rose-600">*</span></label>
                <input type="text" name="subject" value="{{ old('subject') }}" required
                       class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Your email subject line">
                @error('subject')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Message <span class="text-rose-600">*</span></label>

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
                <textarea id="single-email-body" name="message" class="sr-only">{{ old('message') }}</textarea>

                {{-- Quill rich-text editor --}}
                <div id="quill-editor" style="height:420px;" class="rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950"></div>

                {{-- Raw HTML textarea --}}
                <textarea id="html-editor" style="height:420px;" class="hidden w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-3 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" placeholder="Paste or type raw HTML here…"></textarea>

                @error('message')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="button" id="previewBtn"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Preview Email
            </button>
        </div>

        {{-- Section 4: Attachments --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm space-y-3">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">Attachments <span class="text-xs font-normal text-slate-400">(optional)</span></h3>

            <label id="dropZone"
                   class="flex flex-col items-center justify-center gap-2 w-full rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/40 px-6 py-8 cursor-pointer hover:border-indigo-400 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span class="text-sm text-slate-600 dark:text-slate-400">Click to browse or drag &amp; drop files here</span>
                <span class="text-xs text-slate-400">PDF, DOC, DOCX, JPG, PNG, GIF, WEBP — max 10 MB total</span>
                <input id="attachments" name="attachments[]" type="file" multiple
                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp"
                       class="sr-only">
            </label>

            <ul id="fileList" class="space-y-1 text-sm text-slate-700 dark:text-slate-300"></ul>

            @error('attachments')
                <p class="text-xs text-rose-600">{{ $message }}</p>
            @enderror
            @error('attachments.*')
                <p class="text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Section 5: Actions --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <div class="flex flex-wrap items-center gap-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Send Email Now
                </button>
                <a href="{{ url()->previous() }}"
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

    const msgTextarea  = document.getElementById('single-email-body');
    const htmlEditor   = document.getElementById('html-editor');
    const quillEditor  = document.getElementById('quill-editor');
    const tabRichText  = document.getElementById('tabRichText');
    const tabHtml      = document.getElementById('tabHtml');

    // Pre-fill from old() value if any
    const existingMsg = msgTextarea.value.trim();
    if (existingMsg) {
        quill.clipboard.dangerouslyPasteHTML(existingMsg);
        htmlEditor.value = existingMsg;
    }

    // Keep hidden textarea in sync with Quill on every content change
    quill.on('text-change', function () {
        if (htmlEditor.classList.contains('hidden')) {
            msgTextarea.value = quill.root.innerHTML;
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
        msgTextarea.value = htmlEditor.value;
        htmlEditor.classList.remove('hidden');
        quillEditor.classList.add('hidden');
        tabHtml.className     = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${activeTab}`;
        tabRichText.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${inactiveTab}`;
    });

    // Keep hidden textarea in sync when user edits the raw HTML textarea
    htmlEditor?.addEventListener('input', function () {
        if (quillEditor.classList.contains('hidden')) {
            msgTextarea.value = htmlEditor.value;
        }
    });

    // Sync active editor → hidden textarea before form submit, then validate
    document.querySelector('form').addEventListener('submit', function (e) {
        const content = htmlEditor.classList.contains('hidden')
            ? quill.root.innerHTML
            : htmlEditor.value;
        msgTextarea.value = content;

        // Simple non-empty check (strip HTML tags)
        const plainText = content.replace(/<[^>]*>/g, '').trim();
        if (!plainText) {
            e.preventDefault();
            if (!document.getElementById('msg-error')) {
                const err = document.createElement('p');
                err.id = 'msg-error';
                err.className = 'mt-1 text-xs text-rose-600';
                err.textContent = 'Please fill in the message body.';
                msgTextarea.parentNode.insertBefore(err, msgTextarea.nextSibling);
            }
        } else {
            const existing = document.getElementById('msg-error');
            if (existing) existing.remove();
        }
    });

    // ── Preview modal ─────────────────────────────────────────────────────────
    const previewBtn    = document.getElementById('previewBtn');
    const closeBtn      = document.getElementById('closePreviewBtn');
    const previewModal  = document.getElementById('previewModal');
    const previewFrame  = document.getElementById('previewFrame');

    function openPreview() {
        const html = htmlEditor.classList.contains('hidden') ? quill.root.innerHTML : htmlEditor.value;
        const doc = previewFrame?.contentWindow?.document;
        if (!doc) return;
        doc.open();
        doc.write(`<!DOCTYPE html><html><body style="font-family:sans-serif;padding:16px">${html}</body></html>`);
        doc.close();
        previewModal?.classList.remove('hidden');
        previewModal?.classList.add('flex');
    }

    function closePreview() {
        previewModal.classList.add('hidden');
        previewModal.classList.remove('flex');
    }

    previewBtn?.addEventListener('click', openPreview);
    closeBtn?.addEventListener('click', closePreview);
    previewModal?.addEventListener('click', (e) => { if (e.target === previewModal) closePreview(); });

    // ── Attachment drag-and-drop UI ───────────────────────────────────────────
    const dropZone   = document.getElementById('dropZone');
    const fileInput  = document.getElementById('attachments');
    const fileList   = document.getElementById('fileList');

    function renderFileList(files) {
        fileList.innerHTML = '';
        Array.from(files).forEach((file) => {
            const li = document.createElement('li');
            li.className = 'flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs';
            li.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <span class="truncate text-slate-700 dark:text-slate-300">${file.name}</span>
                            <span class="ml-auto shrink-0 text-slate-400">${(file.size / 1024).toFixed(0)} KB</span>`;
            fileList.appendChild(li);
        });
    }

    fileInput?.addEventListener('change', () => renderFileList(fileInput.files));

    dropZone?.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-indigo-500', 'bg-indigo-50/40');
    });

    dropZone?.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-indigo-500', 'bg-indigo-50/40');
    });

    dropZone?.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-indigo-500', 'bg-indigo-50/40');
        if (e.dataTransfer?.files?.length) {
            const dt = new DataTransfer();
            Array.from(e.dataTransfer.files).forEach((f) => dt.items.add(f));
            fileInput.files = dt.files;
            renderFileList(fileInput.files);
        }
    });
</script>
@endpush

@endsection
