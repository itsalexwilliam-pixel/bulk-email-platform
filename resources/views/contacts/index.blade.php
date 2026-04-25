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

        <div class="flex flex-wrap items-center justify-between gap-2">
            <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                <input type="checkbox" id="selectAllContacts" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Select all on page
            </label>
            <div class="flex items-center gap-2">
                <button type="button" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-xs font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">Assign Group</button>
                <button type="button" class="px-3 py-2 rounded-lg border border-rose-300 text-rose-600 text-xs font-medium hover:bg-rose-50 dark:hover:bg-rose-500/10 transition">Delete Selected</button>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
            <table class="w-full text-sm" id="contactsTable">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr class="text-left text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4 w-10"></th>
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Groups</th>
                        <th class="py-3 px-4 w-44">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $contact)
                        <tr class="border-t border-slate-100 dark:border-slate-800 contact-row hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4">
                                <input type="checkbox" class="contact-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-100 contact-name">{{ $contact->name }}</td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300 contact-email">{{ $contact->email }}</td>
                            <td class="py-3 px-4">
                                @forelse($contact->groups as $group)
                                    <span class="inline-flex px-2 py-1 mr-1 mb-1 rounded-full bg-slate-100 dark:bg-slate-800 text-xs text-slate-600 dark:text-slate-300">{{ $group->name }}</span>
                                @empty
                                    <span class="text-xs text-slate-400">No groups</span>
                                @endforelse
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('contacts.edit', $contact) }}"
                                       class="px-3 py-1.5 rounded-lg bg-amber-100 text-amber-700 text-xs font-medium hover:bg-amber-200 transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('contacts.destroy', $contact) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-lg bg-rose-100 text-rose-700 text-xs font-medium hover:bg-rose-200 transition"
                                                onclick="return confirm('Delete this contact?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">No contacts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $contacts->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const searchInput = document.getElementById('contactSearch');
    const rows = document.querySelectorAll('.contact-row');

    searchInput?.addEventListener('input', function () {
        const term = this.value.toLowerCase();
        rows.forEach(row => {
            const name = row.querySelector('.contact-name')?.textContent.toLowerCase() || '';
            const email = row.querySelector('.contact-email')?.textContent.toLowerCase() || '';
            row.style.display = (name.includes(term) || email.includes(term)) ? '' : 'none';
        });
    });

    const selectAll = document.getElementById('selectAllContacts');
    const checkboxes = document.querySelectorAll('.contact-checkbox');
    selectAll?.addEventListener('change', function () {
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>
@endpush
