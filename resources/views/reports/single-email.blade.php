@extends('layouts.app')

@section('page_title', 'Single Email Report')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-2 shadow-sm">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reports.single-email') }}"
               class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium bg-indigo-600 text-white border border-indigo-600">
                Single Email Report
            </a>
            <a href="{{ route('reports.index') }}"
               class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                Campaign Report
            </a>
            <a href="{{ route('reports.warmup') }}"
               class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                Warmup Report
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('reports.single-email') }}" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-5 shadow-sm">
        @if (session('warning'))
            <div class="mb-3 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 px-3 py-2 text-sm">
                {{ session('warning') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div>
                <label for="date_range" class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Date Range</label>
                <select id="date_range" name="date_range" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
                    <option value="today" {{ ($filters['date_range'] ?? '30d') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="7d" {{ ($filters['date_range'] ?? '30d') === '7d' ? 'selected' : '' }}>Last 7 days</option>
                    <option value="30d" {{ ($filters['date_range'] ?? '30d') === '30d' ? 'selected' : '' }}>Last 30 days</option>
                    <option value="custom" {{ ($filters['date_range'] ?? '30d') === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>

            <div>
                <label for="from" class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">From</label>
                <input id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
            </div>

            <div>
                <label for="to" class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">To</label>
                <input id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full inline-flex justify-center items-center rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2">
                    Apply Filters
                </button>
            </div>

            <div class="flex items-end">
                <a href="{{ route('reports.single-email', ['date_range' => '30d']) }}"
                   class="w-full inline-flex justify-center items-center rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm px-4 py-2">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Single Email Activity Log</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <th class="py-2 pr-3">Sent At</th>
                        <th class="py-2 pr-3">To</th>
                        <th class="py-2 pr-3">From Name</th>
                        <th class="py-2 pr-3">Subject</th>
                        <th class="py-2 pr-3">Status</th>
                        <th class="py-2 pr-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($emailLogs as $log)
                        <tr class="border-b border-slate-100 dark:border-slate-800/70">
                            <td class="py-2 pr-3">{{ optional($log->sent_at ?? $log->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="py-2 pr-3">{{ $log->email }}</td>
                            <td class="py-2 pr-3">{{ $log->from_name ?: '-' }}</td>
                            <td class="py-2 pr-3">{{ $log->subject ?: '-' }}</td>
                            <td class="py-2 pr-3 capitalize">{{ $log->status }}</td>
                            <td class="py-2 pr-3 text-right">
                                <button type="button"
                                        class="inline-flex items-center rounded-md border border-slate-300 dark:border-slate-700 px-2.5 py-1 text-xs hover:bg-slate-50 dark:hover:bg-slate-800"
                                        data-preview-url="{{ route('reports.email.show', ['id' => $log->id]) }}"
                                        data-preview-trigger="true">
                                    👁 View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center text-slate-500">No single email activity found for selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $emailLogs->links() }}
        </div>
    </div>
</div>

<div id="emailPreviewModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl w-[95%] max-w-5xl max-h-[90vh] overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-800">
            <h3 class="font-semibold text-slate-900 dark:text-slate-100">Sent Email Preview</h3>
            <button type="button" id="closeEmailPreviewBtn" class="text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">✕</button>
        </div>
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-300" id="emailPreviewMeta"></div>
        <div class="p-4 max-h-[70vh] overflow-auto">
            <iframe id="emailPreviewFrame" class="w-full h-[62vh] border border-slate-200 dark:border-slate-700 rounded-md"></iframe>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const dateRange = document.getElementById('date_range');
    const fromInput = document.getElementById('from');
    const toInput = document.getElementById('to');

    function syncCustomDateEnabled() {
        const isCustom = dateRange && dateRange.value === 'custom';
        if (fromInput) fromInput.disabled = !isCustom;
        if (toInput) toInput.disabled = !isCustom;
    }

    dateRange?.addEventListener('change', syncCustomDateEnabled);
    syncCustomDateEnabled();

    const previewModal = document.getElementById('emailPreviewModal');
    const previewFrame = document.getElementById('emailPreviewFrame');
    const closePreviewBtn = document.getElementById('closeEmailPreviewBtn');
    const previewMeta = document.getElementById('emailPreviewMeta');
    const previewButtons = document.querySelectorAll('[data-preview-trigger="true"]');

    function closePreview() {
        previewModal?.classList.add('hidden');
        previewModal?.classList.remove('flex');
    }

    closePreviewBtn?.addEventListener('click', closePreview);
    previewModal?.addEventListener('click', (e) => {
        if (e.target === previewModal) {
            closePreview();
        }
    });

    previewButtons.forEach((btn) => {
        btn.addEventListener('click', async () => {
            const url = btn.getAttribute('data-preview-url');
            if (!url) return;

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Unable to load email preview.');
                }

                const payload = await response.json();
                const html = payload.body_snapshot || '';
                const doc = previewFrame?.contentWindow?.document;

                if (doc) {
                    doc.open();
                    doc.write(html);
                    doc.close();
                }

                if (previewMeta) {
                    previewMeta.textContent =
                        `To: ${payload.to || '-'} | From: ${payload.from_name || '-'} <${payload.from_email || '-'}> | Subject: ${payload.subject || '-'} | Type: ${payload.type || '-'} | Status: ${payload.status || '-'}`;
                }

                previewModal?.classList.remove('hidden');
                previewModal?.classList.add('flex');
            } catch (error) {
                if (previewMeta) {
                    previewMeta.textContent = 'Failed to load preview.';
                }
            }
        });
    });
})();
</script>
@endpush
