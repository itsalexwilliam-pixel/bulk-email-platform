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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Select Contacts</label>
                    <select name="contact_ids[]" multiple size="8"
                            class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($contacts as $contact)
                            <option value="{{ $contact->id }}" @selected(collect(old('contact_ids'))->contains($contact->id))>
                                {{ $contact->name }} ({{ $contact->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Select Groups</label>
                    <select name="group_ids[]" multiple size="8"
                            class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" @selected(collect(old('group_ids'))->contains($group->id))>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Selected group contacts are merged with manually selected contacts.</p>
                </div>
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
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
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
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#body-editor',
        height: 360,
        menubar: true,
        plugins: 'lists link image table code preview',
        toolbar: 'undo redo | formatselect | bold italic underline | bullist numlist | link table | code preview'
    });

    const previewBtn = document.getElementById('previewBtn');
    const previewWrapper = document.getElementById('previewWrapper');
    const previewBody = document.getElementById('previewBody');

    previewBtn?.addEventListener('click', function () {
        const html = tinymce.get('body-editor') ? tinymce.get('body-editor').getContent() : '';
        previewBody.innerHTML = html;
        previewWrapper.classList.remove('hidden');
    });
</script>
@endpush
