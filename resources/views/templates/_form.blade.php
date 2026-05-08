{{-- Shared form fields for template create/edit --}}
<div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm space-y-4">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Template Name <span class="text-rose-600">*</span></label>
            <input type="text" name="name" value="{{ old('name', $template->name ?? '') }}" required
                   class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="e.g. Welcome Email, Promo Blast">
            @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Category <span class="text-xs text-slate-400">(optional)</span></label>
            <input type="text" name="category" value="{{ old('category', $template->category ?? '') }}"
                   class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="e.g. Newsletter, Promo, Transactional">
            @error('category')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Default Subject <span class="text-xs text-slate-400">(optional — pre-fills campaign subject)</span></label>
        <input type="text" name="subject" value="{{ old('subject', $template->subject ?? '') }}"
               class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
               placeholder="Your email subject line">
        @error('subject')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Email Body <span class="text-rose-600">*</span></label>

        <div class="flex gap-1 mb-2">
            <button type="button" id="tplTabRich"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-600 text-white transition">Rich Text</button>
            <button type="button" id="tplTabHtml"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">HTML</button>
        </div>

        <textarea id="tpl-body" name="body" class="sr-only">{{ old('body', $template->body ?? '') }}</textarea>
        <div id="tpl-quill" style="height:420px;" class="rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950"></div>
        <textarea id="tpl-html-editor" style="height:420px;" class="hidden w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-3 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" placeholder="Paste or type raw HTML here…"></textarea>

        @error('body')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
    const tplQuill = new Quill('#tpl-quill', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1,2,3,4,5,6,false] }],
                [{ font: [] }],[{ size: ['small',false,'large','huge'] }],
                ['bold','italic','underline','strike'],
                [{ color: [] },{ background: [] }],
                [{ align: [] }],
                [{ list: 'ordered' },{ list: 'bullet' }],
                ['link','image'],['blockquote','code-block'],['clean'],
            ],
        },
    });

    const tplBody       = document.getElementById('tpl-body');
    const tplHtmlEditor = document.getElementById('tpl-html-editor');
    const tplQuillEl    = document.getElementById('tpl-quill');
    const tplTabRich    = document.getElementById('tplTabRich');
    const tplTabHtml    = document.getElementById('tplTabHtml');

    const existing = tplBody.value.trim();
    if (existing) {
        tplQuill.clipboard.dangerouslyPasteHTML(existing);
        tplHtmlEditor.value = existing;
    }

    tplQuill.on('text-change', () => {
        if (tplHtmlEditor.classList.contains('hidden')) tplBody.value = tplQuill.root.innerHTML;
    });

    const activeTab   = 'bg-indigo-600 text-white';
    const inactiveTab = 'border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800';

    tplTabRich?.addEventListener('click', () => {
        tplQuill.clipboard.dangerouslyPasteHTML(tplHtmlEditor.value || '');
        tplQuillEl.classList.remove('hidden');
        tplHtmlEditor.classList.add('hidden');
        tplTabRich.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${activeTab}`;
        tplTabHtml.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${inactiveTab}`;
    });

    tplTabHtml?.addEventListener('click', () => {
        tplHtmlEditor.value = tplQuill.root.innerHTML;
        tplBody.value = tplHtmlEditor.value;
        tplHtmlEditor.classList.remove('hidden');
        tplQuillEl.classList.add('hidden');
        tplTabHtml.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${activeTab}`;
        tplTabRich.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${inactiveTab}`;
    });

    tplHtmlEditor?.addEventListener('input', () => {
        if (tplQuillEl.classList.contains('hidden')) tplBody.value = tplHtmlEditor.value;
    });

    document.querySelector('form').addEventListener('submit', function (e) {
        const content = tplHtmlEditor.classList.contains('hidden') ? tplQuill.root.innerHTML : tplHtmlEditor.value;
        tplBody.value = content;
        const plain = content.replace(/<[^>]*>/g, '').trim();
        if (!plain) {
            e.preventDefault();
            if (!document.getElementById('tpl-body-err')) {
                const err = document.createElement('p');
                err.id = 'tpl-body-err';
                err.className = 'mt-1 text-xs text-rose-600';
                err.textContent = 'Please fill in the template body.';
                tplBody.parentNode.insertBefore(err, tplBody.nextSibling);
            }
        } else {
            document.getElementById('tpl-body-err')?.remove();
        }
    });
</script>
@endpush
