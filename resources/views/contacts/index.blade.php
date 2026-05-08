@extends('layouts.app')

@section('page_title', 'Audience / Contacts')

@section('content')
@php
    $totalContacts = $contacts->total();
    $withGroups = \App\Models\Contact::has('groups')->count();
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-saas-stat-card title="Total Contacts" :value="$totalContacts" hint="Across all pages" />
        <x-saas-stat-card title="Assigned to Groups" :value="$withGroups" hint="Segmented contacts" />
        <x-saas-stat-card title="Unassigned" :value="max($totalContacts - $withGroups, 0)" hint="Needs grouping" />
    </div>

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-5 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center gap-3 justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white mr-2">Contacts</h2>
                <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $totalContacts }} total</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('import.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                    Import CSV
                </a>
                <a href="{{ route('groups.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-700 text-white text-sm font-medium hover:bg-slate-800 transition">
                    Manage Lists (Groups)
                </a>
                <a href="{{ route('contacts.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Add Contact
                </a>
            </div>
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-950/30 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-xl border border-indigo-200 dark:border-indigo-900/50 bg-indigo-50/70 dark:bg-indigo-950/30 px-4 py-3 text-sm text-indigo-800 dark:text-indigo-200">
            Recommended flow: <span class="font-semibold">Create a List → Import contacts → Send campaign to the List.</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="md:col-span-2">
                <input id="contactSearch" type="text" placeholder="Search by name or email..."
                       class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <select class="rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option>All Groups</option>
                <option>Has Group</option>
                <option>No Group</option>
            </select>
        </div>

        {{-- Bulk actions toolbar --}}
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-3">
                <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input type="checkbox" id="selectAllPage" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span id="selectAllLabel">Select all on page</span>
                </label>
                {{-- "Select all X contacts" banner (shown when page is fully checked) --}}
                <div id="selectAllBanner" class="hidden text-sm text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 rounded-lg px-3 py-1.5">
                    <span id="selectAllBannerText"></span>
                    <button type="button" id="selectAllGlobalBtn" class="ml-2 font-semibold underline hover:no-underline"></button>
                    <button type="button" id="clearAllGlobalBtn" class="ml-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 underline text-xs hidden">Clear selection</button>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button id="assignGroupBtn" type="button" disabled
                        class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-xs font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Assign Group
                </button>
                <button id="deleteSelectedBtn" type="button" disabled
                        class="px-3 py-2 rounded-lg border border-rose-300 text-rose-600 text-xs font-medium hover:bg-rose-50 dark:hover:bg-rose-500/10 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Delete Selected
                </button>
                <button id="deleteAllBtn" type="button"
                        class="px-3 py-2 rounded-lg border border-rose-300 text-white bg-rose-600 text-xs font-medium hover:bg-rose-700 transition">
                    Delete All Contacts
                </button>
            </div>
        </div>

        {{-- Contacts table --}}
        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
            <table class="w-full text-sm" id="contactsTable">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr class="text-left text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4 w-10"></th>
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Business Name</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Website</th>
                        <th class="py-3 px-4">Groups</th>
                        <th class="py-3 px-4">Tags</th>
                        <th class="py-3 px-4 text-center">Opens</th>
                        <th class="py-3 px-4 w-44">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $contact)
                        <tr class="border-t border-slate-100 dark:border-slate-800 contact-row hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4">
                                <input type="checkbox" data-id="{{ $contact->id }}" class="contact-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-100 contact-name">
                                {{ $contact->name }}
                                @if($contact->is_bounced)
                                    <span class="ml-1 px-1.5 py-0.5 text-[10px] rounded bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-300">bounced</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ $contact->business_name ?: '—' }}</td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300 contact-email">{{ $contact->email }}</td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                                @if ($contact->website)
                                    <a href="{{ $contact->website }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 underline">
                                        Visit
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @forelse($contact->groups as $group)
                                    <span class="inline-flex px-2 py-1 mr-1 mb-1 rounded-full bg-slate-100 dark:bg-slate-800 text-xs text-slate-600 dark:text-slate-300">{{ $group->name }}</span>
                                @empty
                                    <span class="text-xs text-slate-400">No groups</span>
                                @endforelse
                            </td>
                            {{-- Tags --}}
                            <td class="py-3 px-4">
                                <div class="flex flex-wrap gap-1 items-center">
                                    @foreach($contact->tags as $tag)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300">
                                            {{ $tag->tag }}
                                            <form method="POST" action="{{ route('contacts.tags.destroy', [$contact, $tag]) }}" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="hover:text-rose-500 leading-none" title="Remove tag">&times;</button>
                                            </form>
                                        </span>
                                    @endforeach
                                    <button type="button"
                                            onclick="toggleTagForm({{ $contact->id }})"
                                            class="inline-flex items-center justify-center w-5 h-5 rounded-full border border-dashed border-slate-400 text-slate-400 hover:border-violet-500 hover:text-violet-500 transition text-xs leading-none">
                                        +
                                    </button>
                                </div>
                                <form id="tag-form-{{ $contact->id }}"
                                      method="POST"
                                      action="{{ route('contacts.tags.store', $contact) }}"
                                      class="hidden mt-1 flex gap-1">
                                    @csrf
                                    <input type="text" name="tag" placeholder="Add tag..."
                                           maxlength="60"
                                           class="w-28 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-2 py-1 focus:outline-none focus:ring-1 focus:ring-violet-500"
                                           onkeydown="if(event.key==='Escape') toggleTagForm({{ $contact->id }})">
                                    <button type="submit" class="px-2 py-1 rounded-lg bg-violet-600 text-white text-xs hover:bg-violet-700 transition">Add</button>
                                </form>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if(($contact->sent_count ?? 0) > 0)
                                    <div class="flex flex-col items-center gap-0.5">
                                        <span class="text-xs font-semibold {{ ($contact->open_count ?? 0) > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                            {{ $contact->open_count ?? 0 }}
                                        </span>
                                        <span class="text-[10px] text-slate-400">/ {{ $contact->sent_count }} sent</span>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('contacts.edit', $contact) }}"
                                       class="px-3 py-1.5 rounded-lg bg-amber-100 text-amber-700 text-xs font-medium hover:bg-amber-200 transition">
                                        Edit
                                    </a>
                                    @if($contact->is_bounced)
                                        <form method="POST" action="{{ route('contacts.clear-bounced', $contact) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-medium hover:bg-emerald-200 transition"
                                                    onclick="return confirm('Clear bounce flag for {{ $contact->email }}?')">
                                                Unmark
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('contacts.mark-bounced', $contact) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg bg-orange-100 text-orange-700 text-xs font-medium hover:bg-orange-200 transition"
                                                    onclick="return confirm('Mark {{ $contact->email }} as bounced? They will be unsubscribed.')">
                                                Bounced
                                            </button>
                                        </form>
                                    @endif
                                    <button type="submit"
                                            form="delete-contact-{{ $contact->id }}"
                                            class="px-3 py-1.5 rounded-lg bg-rose-100 text-rose-700 text-xs font-medium hover:bg-rose-200 transition"
                                            onclick="return confirm('Delete this contact?')">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-500">No contacts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Individual delete forms --}}
        @foreach($contacts as $contact)
            <form id="delete-contact-{{ $contact->id }}" action="{{ route('contacts.destroy', $contact) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        {{-- Hidden bulk-delete form --}}
        <form id="bulkDeleteForm" method="POST" action="{{ route('contacts.bulk-delete') }}" class="hidden">
            @csrf
            <input type="hidden" name="delete_all" id="bulkDeleteAll" value="0">
            <div id="bulkDeleteIds"></div>
        </form>

        {{-- Hidden bulk-assign-group form --}}
        <form id="bulkAssignForm" method="POST" action="{{ route('contacts.bulk-assign-group') }}" class="hidden">
            @csrf
            <input type="hidden" name="assign_all" id="bulkAssignAll" value="0">
            <input type="hidden" name="group_id" id="bulkAssignGroupId" value="">
            <div id="bulkAssignIds"></div>
        </form>

        <div>
            {{ $contacts->links() }}
        </div>
    </div>
</div>

{{-- Assign Group Modal --}}
<div id="assignGroupModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 w-full max-w-md mx-4 p-6 space-y-4">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Assign Group</h3>
        <p id="assignGroupModalDesc" class="text-sm text-slate-500 dark:text-slate-400"></p>

        <div>
            <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Select Group</label>
            <select id="assignGroupSelect"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">— choose a group —</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" id="assignGroupCancelBtn"
                    class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                Cancel
            </button>
            <button type="button" id="assignGroupConfirmBtn"
                    class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                Assign
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ── Contact Tags ──────────────────────────────────────────────────────────
    function toggleTagForm(contactId) {
        const form = document.getElementById('tag-form-' + contactId);
        if (!form) return;
        form.classList.toggle('hidden');
        if (!form.classList.contains('hidden')) {
            form.querySelector('input[name="tag"]').focus();
        }
    }

    const TOTAL_CONTACTS = {{ $totalContacts }};
    const PAGE_COUNT     = {{ $contacts->count() }};

    // ── Search ────────────────────────────────────────────────────────────────
    const searchInput = document.getElementById('contactSearch');
    const rows = document.querySelectorAll('.contact-row');
    searchInput?.addEventListener('input', function () {
        const term = this.value.toLowerCase();
        rows.forEach(row => {
            const name  = row.querySelector('.contact-name')?.textContent.toLowerCase()  || '';
            const email = row.querySelector('.contact-email')?.textContent.toLowerCase() || '';
            row.style.display = (name.includes(term) || email.includes(term)) ? '' : 'none';
        });
    });

    // ── Selection state ───────────────────────────────────────────────────────
    const selectAllPage     = document.getElementById('selectAllPage');
    const selectAllBanner   = document.getElementById('selectAllBanner');
    const selectAllBannerTxt= document.getElementById('selectAllBannerText');
    const selectAllGlobalBtn= document.getElementById('selectAllGlobalBtn');
    const clearAllGlobalBtn = document.getElementById('clearAllGlobalBtn');
    const checkboxes        = document.querySelectorAll('.contact-checkbox');
    const assignGroupBtn    = document.getElementById('assignGroupBtn');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    const deleteAllBtn      = document.getElementById('deleteAllBtn');

    // isGlobalAll = true means "all X contacts across all pages" are selected
    let isGlobalAll = false;

    function getPageSelectedIds() {
        return Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.dataset.id);
    }

    function getPageSelectedCount() {
        return Array.from(checkboxes).filter(cb => cb.checked).length;
    }

    function syncToolbar() {
        const count = isGlobalAll ? TOTAL_CONTACTS : getPageSelectedCount();
        const hasSelection = count > 0;

        assignGroupBtn.disabled    = !hasSelection;
        deleteSelectedBtn.disabled = !hasSelection;

        deleteSelectedBtn.textContent = hasSelection
            ? `Delete Selected (${count})`
            : 'Delete Selected';
        assignGroupBtn.textContent = hasSelection
            ? `Assign Group (${count})`
            : 'Assign Group';
    }

    function syncBanner() {
        const pageSelected = getPageSelectedCount();
        const allPageSelected = pageSelected === PAGE_COUNT && PAGE_COUNT > 0;

        if (!allPageSelected || TOTAL_CONTACTS <= PAGE_COUNT) {
            // hide banner, reset global
            isGlobalAll = false;
            selectAllBanner.classList.add('hidden');
            clearAllGlobalBtn.classList.add('hidden');
        } else {
            // All on page checked and there are more pages
            selectAllBanner.classList.remove('hidden');
            if (isGlobalAll) {
                selectAllBannerTxt.textContent = `All ${TOTAL_CONTACTS} contacts are selected.`;
                selectAllGlobalBtn.textContent = '';
                clearAllGlobalBtn.classList.remove('hidden');
            } else {
                selectAllBannerTxt.textContent = `All ${PAGE_COUNT} contacts on this page are selected.`;
                selectAllGlobalBtn.textContent = `Select all ${TOTAL_CONTACTS} contacts`;
                clearAllGlobalBtn.classList.add('hidden');
            }
        }
    }

    selectAllPage?.addEventListener('change', function () {
        checkboxes.forEach(cb => { cb.checked = this.checked; });
        if (!this.checked) isGlobalAll = false;
        syncBanner();
        syncToolbar();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            const allChecked = getPageSelectedCount() === checkboxes.length;
            if (selectAllPage) selectAllPage.checked = allChecked;
            if (!allChecked) isGlobalAll = false;
            syncBanner();
            syncToolbar();
        });
    });

    selectAllGlobalBtn?.addEventListener('click', function () {
        isGlobalAll = true;
        syncBanner();
        syncToolbar();
    });

    clearAllGlobalBtn?.addEventListener('click', function () {
        isGlobalAll = false;
        checkboxes.forEach(cb => { cb.checked = false; });
        if (selectAllPage) selectAllPage.checked = false;
        syncBanner();
        syncToolbar();
    });

    // ── Build hidden id inputs for a container div ────────────────────────────
    function populateIds(containerEl, ids) {
        containerEl.innerHTML = '';
        ids.forEach(id => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = 'ids[]';
            inp.value = id;
            containerEl.appendChild(inp);
        });
    }

    // ── Delete Selected ───────────────────────────────────────────────────────
    deleteSelectedBtn?.addEventListener('click', function () {
        const count = isGlobalAll ? TOTAL_CONTACTS : getPageSelectedCount();
        if (count === 0) return;

        if (!confirm(`Are you sure you want to delete ${count} contact(s)?`)) return;

        const form      = document.getElementById('bulkDeleteForm');
        const deleteAll = document.getElementById('bulkDeleteAll');
        const idsDiv    = document.getElementById('bulkDeleteIds');

        if (isGlobalAll) {
            deleteAll.value = '1';
            idsDiv.innerHTML = '';
        } else {
            deleteAll.value = '0';
            populateIds(idsDiv, getPageSelectedIds());
        }

        form.submit();
    });

    // ── Delete All ────────────────────────────────────────────────────────────
    deleteAllBtn?.addEventListener('click', function () {
        if (!confirm(`Are you sure you want to delete ALL ${TOTAL_CONTACTS} contacts? This cannot be undone.`)) return;

        const form      = document.getElementById('bulkDeleteForm');
        const deleteAll = document.getElementById('bulkDeleteAll');
        const idsDiv    = document.getElementById('bulkDeleteIds');

        deleteAll.value  = '1';
        idsDiv.innerHTML = '';
        form.submit();
    });

    // ── Assign Group Modal ────────────────────────────────────────────────────
    const modal             = document.getElementById('assignGroupModal');
    const assignGroupSelect = document.getElementById('assignGroupSelect');
    const assignGroupDesc   = document.getElementById('assignGroupModalDesc');
    const assignGroupCancel = document.getElementById('assignGroupCancelBtn');
    const assignGroupConfirm= document.getElementById('assignGroupConfirmBtn');

    assignGroupBtn?.addEventListener('click', function () {
        const count = isGlobalAll ? TOTAL_CONTACTS : getPageSelectedCount();
        assignGroupDesc.textContent = `Assign a group to ${count} selected contact(s). Existing group assignments will be kept.`;
        assignGroupSelect.value = '';
        modal.classList.remove('hidden');
    });

    assignGroupCancel?.addEventListener('click', function () {
        modal.classList.add('hidden');
    });

    // Close modal on backdrop click
    modal?.addEventListener('click', function (e) {
        if (e.target === modal) modal.classList.add('hidden');
    });

    assignGroupConfirm?.addEventListener('click', function () {
        const groupId = assignGroupSelect.value;
        if (!groupId) {
            alert('Please select a group.');
            return;
        }

        const form       = document.getElementById('bulkAssignForm');
        const assignAll  = document.getElementById('bulkAssignAll');
        const groupIdInp = document.getElementById('bulkAssignGroupId');
        const idsDiv     = document.getElementById('bulkAssignIds');

        groupIdInp.value = groupId;

        if (isGlobalAll) {
            assignAll.value  = '1';
            idsDiv.innerHTML = '';
        } else {
            assignAll.value = '0';
            populateIds(idsDiv, getPageSelectedIds());
        }

        modal.classList.add('hidden');
        form.submit();
    });

    // ── Init ──────────────────────────────────────────────────────────────────
    syncToolbar();
</script>
@endpush
