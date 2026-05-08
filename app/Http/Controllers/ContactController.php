<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\EmailOpen;
use App\Models\EmailQueue;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::with('groups')->latest()->paginate(10);
        $groups   = Group::orderBy('name')->get();

        // Open counts per contact
        $contactIds = $contacts->pluck('id');
        $openCounts = DB::table('email_opens')
            ->join('email_queue', 'email_queue.id', '=', 'email_opens.email_queue_id')
            ->whereIn('email_queue.contact_id', $contactIds)
            ->select('email_queue.contact_id', DB::raw('COUNT(email_opens.id) as open_count'))
            ->groupBy('email_queue.contact_id')
            ->pluck('open_count', 'contact_id');

        // Emails sent per contact
        $sentCounts = DB::table('email_queue')
            ->whereIn('contact_id', $contactIds)
            ->where('status', 'sent')
            ->select('contact_id', DB::raw('COUNT(*) as sent_count'))
            ->groupBy('contact_id')
            ->pluck('sent_count', 'contact_id');

        foreach ($contacts as $contact) {
            $contact->open_count = (int) ($openCounts[$contact->id] ?? 0);
            $contact->sent_count = (int) ($sentCounts[$contact->id] ?? 0);
        }

        return view('contacts.index', compact('contacts', 'groups'));
    }

    public function create()
    {
        $groups = Group::orderBy('name')->get();
        return view('contacts.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:contacts,email'],
            'website' => ['nullable', 'url', 'max:255'],
            'groups' => ['nullable', 'array'],
            'groups.*' => ['exists:groups,id'],
        ]);

        $accountId = (int) ($request->user()?->account_id ?? 0);

        $contact = Contact::create([
            'account_id' => $accountId,
            'name' => $data['name'],
            'business_name' => $data['business_name'] ?? null,
            'email' => $data['email'],
            'website' => $data['website'] ?? null,
        ]);

        $contact->groups()->sync($data['groups'] ?? []);

        return redirect()->route('contacts.index')->with('success', 'Contact created successfully.');
    }

    public function edit(Contact $contact)
    {
        $groups = Group::orderBy('name')->get();
        $selectedGroups = $contact->groups()->pluck('groups.id')->toArray();

        return view('contacts.edit', compact('contact', 'groups', 'selectedGroups'));
    }

    public function update(Request $request, Contact $contact)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('contacts', 'email')->ignore($contact->id),
            ],
            'website' => ['nullable', 'url', 'max:255'],
            'groups' => ['nullable', 'array'],
            'groups.*' => ['exists:groups,id'],
        ]);

        $contact->update([
            'account_id' => (int) ($request->user()?->account_id ?? $contact->account_id),
            'name' => $data['name'],
            'business_name' => $data['business_name'] ?? null,
            'email' => $data['email'],
            'website' => $data['website'] ?? null,
        ]);

        $contact->groups()->sync($data['groups'] ?? []);

        return redirect()->route('contacts.index')->with('success', 'Contact updated successfully.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('contacts.index')->with('success', 'Contact deleted successfully.');
    }

    public function bulkAssignGroup(Request $request)
    {
        $request->merge(['assign_all' => filter_var($request->input('assign_all', '0'), FILTER_VALIDATE_BOOLEAN)]);

        $data = $request->validate([
            'ids'        => ['sometimes', 'array'],
            'ids.*'      => ['integer', 'distinct', 'exists:contacts,id'],
            'assign_all' => ['sometimes', 'boolean'],
            'group_id'   => ['required', 'integer', 'exists:groups,id'],
        ]);

        $accountId = (int) (auth()->user()?->account_id ?? 0);
        $assignAll = (bool) ($data['assign_all'] ?? false);
        $groupId   = (int) $data['group_id'];

        if ($assignAll) {
            $validIds = Contact::where('account_id', $accountId)->pluck('id')->toArray();
        } else {
            $ids = $data['ids'] ?? [];
            $validIds = Contact::where('account_id', $accountId)
                ->whereIn('id', $ids)
                ->pluck('id')
                ->toArray();
        }

        if (count($validIds) === 0) {
            return back()->withErrors(['ids' => 'No valid contacts selected.']);
        }

        // Attach the group to all selected contacts (without detaching existing groups)
        $contacts = Contact::whereIn('id', $validIds)->get();
        foreach ($contacts as $contact) {
            $contact->groups()->syncWithoutDetaching([$groupId]);
        }

        return back()->with('success', count($validIds) . ' contact(s) assigned to group successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $request->merge(['delete_all' => filter_var($request->input('delete_all', '0'), FILTER_VALIDATE_BOOLEAN)]);

        $data = $request->validate([
            'ids' => ['sometimes', 'array'],
            'ids.*' => ['integer', 'distinct', 'exists:contacts,id'],
            'delete_all' => ['sometimes', 'boolean'],
        ]);

        $accountId = (int) (auth()->user()?->account_id ?? 0);
        $deleteAll = (bool) ($data['delete_all'] ?? false);

        $ids = $data['ids'] ?? [];

        if ($deleteAll) {
            $validIds = Contact::where('account_id', $accountId)->pluck('id')->toArray();
        } else {
            $validIds = Contact::where('account_id', $accountId)
                ->whereIn('id', $ids)
                ->pluck('id')
                ->toArray();
        }

        if (count($validIds) === 0) {
            return back()->withErrors([
                'ids' => 'No valid contacts selected for deletion.',
            ]);
        }

        $deleted = Contact::whereIn('id', $validIds)->delete();

        Log::info('Bulk contact delete completed', [
            'account_id' => $accountId,
            'incoming_ids' => $ids,
            'valid_ids' => $validIds,
            'delete_all' => $deleteAll,
            'count_deleted' => $deleted,
        ]);

        return back()->with('success', "{$deleted} contact(s) deleted successfully.");
    }
}
