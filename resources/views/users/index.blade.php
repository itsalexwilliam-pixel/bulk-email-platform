@extends('layouts.app')

@section('page_title', 'Users')

@section('content')
<div class="p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Users</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage administrator, manager, and operator accounts.</p>
            </div>

            <a href="{{ route('users.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create User
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-950/30 dark:border-emerald-800 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 dark:bg-rose-950/30 dark:border-rose-800 px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">

            <form method="POST" action="{{ route('users.bulk-delete') }}" id="bulkDeleteForm">
                @csrf

                <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 border-b border-slate-200 dark:border-slate-800">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <input type="checkbox" id="selectAllUsers" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Select all on page
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="delete_all" id="deleteAllInput" value="0">
                        <button id="deleteSelectedBtn" type="submit" disabled
                                class="px-3 py-2 rounded-lg border border-rose-300 text-rose-600 text-xs font-medium hover:bg-rose-50 dark:hover:bg-rose-500/10 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            Delete Selected
                        </button>
                        <button id="deleteAllBtn" type="button"
                                class="px-3 py-2 rounded-lg border border-rose-300 text-white bg-rose-600 text-xs font-medium hover:bg-rose-700 transition">
                            Delete All Users
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800" id="usersTable">
                        <thead class="bg-slate-50 dark:bg-slate-950/50">
                            <tr>
                                <th class="px-4 py-3.5 w-10"></th>
                                <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Name</th>
                                <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Email</th>
                                <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Role</th>
                                <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Created</th>
                                <th class="px-4 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($users as $user)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition user-row">
                                    <td class="px-4 py-3.5">
                                        @if ($user->id !== auth()->id())
                                            <input type="checkbox" name="ids[]" value="{{ $user->id }}"
                                                   class="user-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-sm font-medium text-slate-900 dark:text-slate-100">
                                        {{ $user->name }}
                                        @if ($user->id === auth()->id())
                                            <span class="ml-1 text-xs text-slate-400">(you)</span>
                                        @endif
                                        @if (!$user->account_id)
                                            <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">No account</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-sm text-slate-600 dark:text-slate-300">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-4 py-3.5 text-sm">
                                        @php
                                            $roleColors = match($user->role) {
                                                'admin' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
                                                'manager' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
                                                default => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $roleColors }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-sm text-slate-600 dark:text-slate-300">
                                        {{ optional($user->created_at)->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('users.edit', $user) }}"
                                               class="inline-flex items-center rounded-lg border border-slate-300 dark:border-slate-700 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                                Edit
                                            </a>
                                            @if ($user->id !== auth()->id())
                                                <button type="submit" form="delete-user-{{ $user->id }}"
                                                        class="inline-flex items-center rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition"
                                                        onclick="return confirm('Delete user {{ addslashes($user->name) }}?')">
                                                    Delete
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                        No users found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            @foreach ($users as $user)
                @if ($user->id !== auth()->id())
                    <form id="delete-user-{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
            @endforeach

            @if ($users->hasPages())
                <div class="border-t border-slate-200 dark:border-slate-800 px-4 py-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const selectAll = document.getElementById('selectAllUsers');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    const deleteAllBtn = document.getElementById('deleteAllBtn');
    const deleteAllInput = document.getElementById('deleteAllInput');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');

    function getSelectedCount() {
        return Array.from(checkboxes).filter(cb => cb.checked).length;
    }

    function syncBulkDeleteState() {
        const count = getSelectedCount();
        if (deleteSelectedBtn) {
            deleteSelectedBtn.disabled = count === 0;
            deleteSelectedBtn.textContent = count > 0 ? `Delete Selected (${count})` : 'Delete Selected';
        }
    }

    selectAll?.addEventListener('change', function () {
        checkboxes.forEach(cb => { cb.checked = this.checked; });
        syncBulkDeleteState();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            if (!cb.checked && selectAll) selectAll.checked = false;
            else if (selectAll) selectAll.checked = getSelectedCount() === checkboxes.length;
            syncBulkDeleteState();
        });
    });

    bulkDeleteForm?.addEventListener('submit', function (e) {
        const deleteAll = deleteAllInput?.value === '1';
        const count = getSelectedCount();

        if (!deleteAll && count === 0) { e.preventDefault(); return; }

        const msg = deleteAll
            ? 'Are you sure you want to delete ALL users (except yourself)? This cannot be undone.'
            : `Are you sure you want to delete ${count} user(s)?`;

        if (!confirm(msg)) {
            e.preventDefault();
            deleteAllInput.value = '0';
            return;
        }

        if (deleteSelectedBtn) {
            deleteSelectedBtn.disabled = true;
            deleteSelectedBtn.textContent = deleteAll ? 'Deleting all...' : `Deleting ${count}...`;
        }
    });

    deleteAllBtn?.addEventListener('click', function () {
        if (!deleteAllInput || !bulkDeleteForm) return;
        deleteAllInput.value = '1';
        if (typeof bulkDeleteForm.requestSubmit === 'function') {
            bulkDeleteForm.requestSubmit();
        } else {
            bulkDeleteForm.submit();
        }
    });

    syncBulkDeleteState();
</script>
@endpush
