@extends('layouts.app')

@section('page_title', 'Drip: ' . $drip->name)

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $drip->name }}</h2>
                    @php
                        $statusColors = [
                            'draft'  => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
                            'active' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                            'paused' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                        ];
                    @endphp
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase {{ $statusColors[$drip->status] ?? '' }}">
                        {{ $drip->status }}
                    </span>
                </div>
                @if($drip->description)
                    <p class="text-sm text-slate-500 mt-1">{{ $drip->description }}</p>
                @endif
                @if($drip->group)
                    <p class="text-xs text-slate-400 mt-1">Auto-enroll from: <span class="font-medium text-slate-600 dark:text-slate-300">{{ $drip->group->name }}</span></p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($drip->status !== 'active')
                    <form method="POST" action="{{ route('drip.activate', $drip) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 transition"
                                onclick="return confirm('Activate this drip? Contacts in the linked group will be enrolled now.')">
                            Activate
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('drip.pause', $drip) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 text-white text-sm font-medium hover:bg-amber-700 transition">
                            Pause
                        </button>
                    </form>
                @endif
                <a href="{{ route('drip.edit', $drip) }}" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Edit Settings
                </a>
                <a href="{{ route('drip.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    ← All Drips
                </a>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-950/30 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500 mb-1">Steps</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $drip->steps->count() }}</p>
        </div>
        <div class="rounded-2xl border border-teal-200 dark:border-teal-800/50 bg-teal-50/50 dark:bg-teal-950/20 p-4 shadow-sm">
            <p class="text-xs font-medium text-teal-600 dark:text-teal-400 mb-1">Total Enrolled</p>
            <p class="text-2xl font-bold text-teal-700 dark:text-teal-300">{{ $enrollmentCount }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 dark:border-emerald-800/50 bg-emerald-50/50 dark:bg-emerald-950/20 p-4 shadow-sm">
            <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 mb-1">Active</p>
            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $activeCount }}</p>
        </div>
    </div>

    {{-- Steps list --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Email Steps</h3>
            <button type="button" onclick="toggleAddStep()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-600 text-white text-xs font-medium hover:bg-teal-700 transition">
                + Add Step
            </button>
        </div>

        {{-- Add step form (hidden by default) --}}
        <div id="addStepForm" class="hidden border-b border-slate-200 dark:border-slate-800 p-5 bg-teal-50/40 dark:bg-teal-950/10 space-y-4">
            <h4 class="text-sm font-semibold text-teal-900 dark:text-teal-200">New Step (Position {{ $drip->steps->count() + 1 }})</h4>
            <form method="POST" action="{{ route('drip.steps.store', $drip) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Subject <span class="text-rose-500">*</span></label>
                        <input type="text" name="subject" required
                               class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                               placeholder="Email subject…">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Send after (days) <span class="text-rose-500">*</span></label>
                        <input type="number" name="delay_days" min="0" max="365" value="{{ $drip->steps->count() === 0 ? 0 : 1 }}" required
                               class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <p class="mt-1 text-xs text-slate-500">Days after the previous step (0 = send immediately for Step 1).</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Email Body <span class="text-rose-500">*</span></label>
                    <div class="flex gap-1 mb-2">
                        <button type="button" id="stepTabRich" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-teal-600 text-white transition">✏️ Rich Text</button>
                        <button type="button" id="stepTabHtml" class="px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">&lt;/&gt; HTML</button>
                    </div>
                    <textarea name="body" id="step-body-textarea" class="sr-only"></textarea>
                    <div id="step-quill" style="height:280px;" class="rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950"></div>
                    <textarea id="step-html-editor" style="height:280px;" class="hidden w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-3 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none" placeholder="Paste HTML here…"></textarea>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-teal-600 text-white text-sm font-medium hover:bg-teal-700 transition">Save Step</button>
                    <button type="button" onclick="toggleAddStep()" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">Cancel</button>
                </div>
            </form>
        </div>

        {{-- Steps table --}}
        @forelse($drip->steps as $step)
            <div class="border-b border-slate-100 dark:border-slate-800 last:border-b-0">
                {{-- Collapsed view --}}
                <div class="flex items-center gap-3 px-5 py-4">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 flex items-center justify-center text-xs font-bold">
                        {{ $step->position }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-100 truncate">{{ $step->subject }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            @if($step->position === 1 && $step->delay_days === 0)
                                Send immediately on enrollment
                            @else
                                Send {{ $step->delay_days }} day{{ $step->delay_days !== 1 ? 's' : '' }} after previous step
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button type="button"
                                onclick="toggleEditStep({{ $step->id }})"
                                class="px-3 py-1.5 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-medium hover:bg-indigo-200 transition">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('drip.steps.destroy', [$drip, $step]) }}" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="px-3 py-1.5 rounded-lg bg-rose-100 text-rose-700 text-xs font-medium hover:bg-rose-200 transition"
                                    onclick="return confirm('Delete this step?')">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
                {{-- Inline edit form --}}
                <div id="edit-step-{{ $step->id }}" class="hidden bg-slate-50/60 dark:bg-slate-800/30 border-t border-slate-200 dark:border-slate-700 px-5 py-4 space-y-4">
                    <form method="POST" action="{{ route('drip.steps.update', [$drip, $step]) }}" class="space-y-4">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Subject</label>
                                <input type="text" name="subject" value="{{ $step->subject }}" required
                                       class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Delay (days)</label>
                                <input type="number" name="delay_days" min="0" max="365" value="{{ $step->delay_days }}" required
                                       class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Email Body</label>
                            <div class="flex gap-1 mb-2">
                                <button type="button" id="editTabRich-{{ $step->id }}" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-teal-600 text-white transition">✏️ Rich Text</button>
                                <button type="button" id="editTabHtml-{{ $step->id }}" class="px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">&lt;/&gt; HTML</button>
                            </div>
                            <textarea name="body" id="edit-body-ta-{{ $step->id }}" class="sr-only">{!! str_replace('</textarea>', '<\/textarea>', $step->body) !!}</textarea>
                            <div id="edit-quill-{{ $step->id }}" style="height:260px;" class="rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950"></div>
                            <textarea id="edit-html-{{ $step->id }}" style="height:260px;" class="hidden w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-3 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none"></textarea>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="submit" class="px-4 py-2 rounded-xl bg-teal-600 text-white text-sm font-medium hover:bg-teal-700 transition">Update Step</button>
                            <button type="button" onclick="toggleEditStep({{ $step->id }})" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="py-10 text-center text-slate-400">
                <p class="text-sm">No steps yet. Add your first email step above.</p>
            </div>
        @endforelse
    </div>

    {{-- Enrollments --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Active Enrollments</h3>
        </div>
        @php
            $enrollments = $drip->enrollments()->with('contact')->where('status', 'active')->latest()->take(20)->get();
        @endphp
        @if($enrollments->isEmpty())
            <p class="px-5 py-6 text-sm text-slate-400">No active enrollments. Activate the drip to enroll contacts from the linked group.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr class="text-left text-xs text-slate-500 dark:text-slate-400">
                            <th class="py-2.5 px-4">Contact</th>
                            <th class="py-2.5 px-4">Next Step</th>
                            <th class="py-2.5 px-4">Send At</th>
                            <th class="py-2.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrollments as $enrollment)
                            <tr class="border-t border-slate-100 dark:border-slate-800 hover:bg-slate-50/60 dark:hover:bg-slate-800/20 transition">
                                <td class="py-2.5 px-4 font-medium text-slate-800 dark:text-slate-100">
                                    {{ $enrollment->contact?->email ?? '—' }}
                                </td>
                                <td class="py-2.5 px-4 text-slate-600 dark:text-slate-300">Step {{ $enrollment->current_step }}</td>
                                <td class="py-2.5 px-4 text-slate-500 dark:text-slate-400">
                                    {{ $enrollment->next_send_at ? $enrollment->next_send_at->format('Y-m-d H:i') : '—' }}
                                </td>
                                <td class="py-2.5 px-4 text-right">
                                    <form method="POST" action="{{ route('drip.unenroll', [$drip, $enrollment]) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="px-2.5 py-1 rounded-lg bg-rose-100 text-rose-700 text-xs font-medium hover:bg-rose-200 transition"
                                                onclick="return confirm('Unenroll this contact?')">
                                            Unenroll
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
    // ── Add Step form Quill ───────────────────────────────────────────────────
    let addStepVisible = false;
    const addStepForm  = document.getElementById('addStepForm');

    function toggleAddStep() {
        addStepVisible = !addStepVisible;
        addStepForm.classList.toggle('hidden', !addStepVisible);
    }

    const stepQuill = new Quill('#step-quill', {
        theme: 'snow',
        modules: { toolbar: [
            [{ header: [1,2,3,false] }], ['bold','italic','underline'],
            [{ color: [] }],[{ align: [] }],[{ list: 'ordered' },{ list: 'bullet' }],
            ['link','image'],['clean']
        ]}
    });

    const stepBodyTa  = document.getElementById('step-body-textarea');
    const stepHtmlEd  = document.getElementById('step-html-editor');
    const stepQuillEl = document.getElementById('step-quill');

    stepQuill.on('text-change', () => {
        if (stepHtmlEd.classList.contains('hidden')) stepBodyTa.value = stepQuill.root.innerHTML;
    });
    stepHtmlEd.addEventListener('input', () => {
        if (stepQuillEl.classList.contains('hidden')) stepBodyTa.value = stepHtmlEd.value;
    });

    const stepTabRich  = document.getElementById('stepTabRich');
    const stepTabHtml  = document.getElementById('stepTabHtml');
    const tActive   = 'bg-teal-600 text-white';
    const tInactive = 'border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800';

    stepTabRich?.addEventListener('click', () => {
        stepQuill.clipboard.dangerouslyPasteHTML(stepHtmlEd.value || '');
        stepQuillEl.classList.remove('hidden'); stepHtmlEd.classList.add('hidden');
        stepTabRich.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${tActive}`;
        stepTabHtml.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${tInactive}`;
    });
    stepTabHtml?.addEventListener('click', () => {
        stepHtmlEd.value = stepQuill.root.innerHTML; stepBodyTa.value = stepHtmlEd.value;
        stepHtmlEd.classList.remove('hidden'); stepQuillEl.classList.add('hidden');
        stepTabHtml.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${tActive}`;
        stepTabRich.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${tInactive}`;
    });

    // Sync before add-step form submit
    addStepForm?.querySelector('form')?.addEventListener('submit', () => {
        stepBodyTa.value = stepHtmlEd.classList.contains('hidden') ? stepQuill.root.innerHTML : stepHtmlEd.value;
    });

    // ── Per-step edit Quill instances ─────────────────────────────────────────
    const editQuills = {};

    function toggleEditStep(stepId) {
        const el = document.getElementById(`edit-step-${stepId}`);
        if (!el) return;
        const wasHidden = el.classList.contains('hidden');
        el.classList.toggle('hidden', !wasHidden);

        if (wasHidden && !editQuills[stepId]) {
            // Initialize Quill for this step when opened for the first time
            const q = new Quill(`#edit-quill-${stepId}`, {
                theme: 'snow',
                modules: { toolbar: [
                    [{ header: [1,2,3,false] }],['bold','italic','underline'],
                    [{ color: [] }],[{ align: [] }],[{ list: 'ordered'},{ list: 'bullet'}],
                    ['link','image'],['clean']
                ]}
            });
            editQuills[stepId] = q;

            const ta   = document.getElementById(`edit-body-ta-${stepId}`);
            const html = document.getElementById(`edit-html-${stepId}`);
            const qEl  = document.getElementById(`edit-quill-${stepId}`);

            // Pre-fill
            let existing = ta.value.trim();
            if (existing) {
                if (existing.includes('&lt;') || existing.includes('&gt;')) {
                    const tmp = document.createElement('textarea');
                    tmp.innerHTML = existing;
                    existing = tmp.value;
                    ta.value = existing;
                }
                q.clipboard.dangerouslyPasteHTML(existing);
                html.value = existing;
            }

            q.on('text-change', () => {
                if (html.classList.contains('hidden')) ta.value = q.root.innerHTML;
            });
            html.addEventListener('input', () => {
                if (qEl.classList.contains('hidden')) ta.value = html.value;
            });

            const richBtn = document.getElementById(`editTabRich-${stepId}`);
            const htmlBtn = document.getElementById(`editTabHtml-${stepId}`);

            richBtn?.addEventListener('click', () => {
                q.clipboard.dangerouslyPasteHTML(html.value || '');
                qEl.classList.remove('hidden'); html.classList.add('hidden');
                richBtn.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${tActive}`;
                htmlBtn.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${tInactive}`;
            });
            htmlBtn?.addEventListener('click', () => {
                html.value = q.root.innerHTML; ta.value = html.value;
                html.classList.remove('hidden'); qEl.classList.add('hidden');
                htmlBtn.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${tActive}`;
                richBtn.className = `px-3 py-1.5 rounded-lg text-xs font-medium transition ${tInactive}`;
            });

            // Sync before submit
            el.querySelector('form')?.addEventListener('submit', () => {
                ta.value = html.classList.contains('hidden') ? q.root.innerHTML : html.value;
            });
        }
    }
</script>
@endpush
