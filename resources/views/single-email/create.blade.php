@extends('layouts.app')

@section('page_title', 'Send Single Email')

@section('content')
<div class="p-6 lg:p-8">
    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Send Single Email</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Send a one-off tracked email immediately using a selected SMTP server.</p>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->has('single_email'))
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 px-4 py-3">
                {{ $errors->first('single_email') }}
            </div>
        @endif

        <form method="POST" action="{{ route('single-email.store') }}" enctype="multipart/form-data"
              class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 lg:p-8 space-y-5">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div>
                    <x-input-label for="to" value="To (Email)" />
                    <x-text-input id="to" name="to" type="email" class="mt-1 block w-full" :value="old('to')" required />
                    <x-input-error :messages="$errors->get('to')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="smtp_server_id" value="SMTP Server" />
                    <select id="smtp_server_id" name="smtp_server_id" required
                            class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select SMTP server</option>
                        @foreach ($smtpServers as $server)
                            <option value="{{ $server->id }}" @selected((string) old('smtp_server_id') === (string) $server->id)>
                                {{ $server->name }} ({{ $server->host }}:{{ $server->port }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('smtp_server_id')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="subject" value="Subject" />
                <x-text-input id="subject" name="subject" type="text" class="mt-1 block w-full" :value="old('subject')" required />
                <x-input-error :messages="$errors->get('subject')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="message" value="Message (HTML supported)" />
                <textarea id="message" name="message" rows="12" required
                          class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500">{{ old('message') }}</textarea>
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div>
                    <x-input-label for="from_name" value="From Name (optional override)" />
                    <x-text-input id="from_name" name="from_name" type="text" class="mt-1 block w-full" :value="old('from_name')" />
                    <x-input-error :messages="$errors->get('from_name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="from_email" value="From Email (optional override)" />
                    <x-text-input id="from_email" name="from_email" type="email" class="mt-1 block w-full" :value="old('from_email')" />
                    <x-input-error :messages="$errors->get('from_email')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="attachments" value="Attachments (optional, multiple)" />
                <input id="attachments" name="attachments[]" type="file" multiple
                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp"
                       class="mt-1 block w-full rounded-md border border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium dark:file:bg-slate-800">
                <p class="text-xs text-slate-500 mt-1">Allowed: PDF, DOC, DOCX, JPG, PNG, GIF, WEBP. Max 10MB total.</p>
                <x-input-error :messages="$errors->get('attachments')" class="mt-2" />
                <x-input-error :messages="$errors->get('attachments.*')" class="mt-2" />
            </div>

            <div class="flex items-center gap-3">
                <button type="button" id="previewBtn"
                        class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100 rounded-md text-sm font-medium">
                    Preview
                </button>

                <x-primary-button>
                    Send Email Now
                </x-primary-button>
            </div>
        </form>
    </div>
</div>

<div id="previewModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl w-[95%] max-w-4xl max-h-[85vh] overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-800">
            <h3 class="font-semibold text-slate-900 dark:text-slate-100">Email Preview</h3>
            <button type="button" id="closePreviewBtn" class="text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">✕</button>
        </div>
        <div class="p-4 max-h-[70vh] overflow-auto">
            <iframe id="previewFrame" class="w-full h-[60vh] border border-slate-200 dark:border-slate-700 rounded-md"></iframe>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const previewBtn = document.getElementById('previewBtn');
        const closePreviewBtn = document.getElementById('closePreviewBtn');
        const previewModal = document.getElementById('previewModal');
        const previewFrame = document.getElementById('previewFrame');
        const messageInput = document.getElementById('message');

        previewBtn?.addEventListener('click', () => {
            const html = messageInput?.value || '';
            const doc = previewFrame.contentWindow?.document;
            if (!doc) return;
            doc.open();
            doc.write(html);
            doc.close();
            previewModal.classList.remove('hidden');
            previewModal.classList.add('flex');
        });

        closePreviewBtn?.addEventListener('click', () => {
            previewModal.classList.add('hidden');
            previewModal.classList.remove('flex');
        });

        previewModal?.addEventListener('click', (e) => {
            if (e.target === previewModal) {
                previewModal.classList.add('hidden');
                previewModal.classList.remove('flex');
            }
        });
    })();
</script>
@endpush

@endsection
