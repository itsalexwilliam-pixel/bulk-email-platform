<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Group;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::withCount('contacts')->latest()->paginate(10);

        return view('campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $contacts = Contact::orderBy('name')->get();
        $groups = Group::orderBy('name')->get();

        return view('campaigns.create', compact('contacts', 'groups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'contact_ids' => ['nullable', 'array'],
            'contact_ids.*' => ['integer', 'exists:contacts,id'],
            'group_ids' => ['nullable', 'array'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
        ]);

        $campaign = Campaign::create([
            'name' => $data['name'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'status' => !empty($data['scheduled_at']) ? 'scheduled' : 'draft',
        ]);

        $contactIds = collect($data['contact_ids'] ?? []);
        $groupContactIds = Contact::whereHas('groups', function ($query) use ($data) {
            $query->whereIn('groups.id', $data['group_ids'] ?? []);
        })->pluck('contacts.id');

        $finalIds = $contactIds->merge($groupContactIds)->unique()->values()->all();
        $campaign->contacts()->sync($finalIds);

        return redirect()->route('campaigns.index')->with('success', 'Campaign created successfully.');
    }

    public function edit(Campaign $campaign)
    {
        $contacts = Contact::orderBy('name')->get();
        $groups = Group::orderBy('name')->get();
        $selectedContactIds = $campaign->contacts()->pluck('contacts.id')->toArray();

        return view('campaigns.edit', compact('campaign', 'contacts', 'groups', 'selectedContactIds'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'contact_ids' => ['nullable', 'array'],
            'contact_ids.*' => ['integer', 'exists:contacts,id'],
            'group_ids' => ['nullable', 'array'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
        ]);

        $campaign->update([
            'name' => $data['name'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'status' => !empty($data['scheduled_at']) ? 'scheduled' : 'draft',
        ]);

        $contactIds = collect($data['contact_ids'] ?? []);
        $groupContactIds = Contact::whereHas('groups', function ($query) use ($data) {
            $query->whereIn('groups.id', $data['group_ids'] ?? []);
        })->pluck('contacts.id');

        $finalIds = $contactIds->merge($groupContactIds)->unique()->values()->all();
        $campaign->contacts()->sync($finalIds);

        return redirect()->route('campaigns.index')->with('success', 'Campaign updated successfully.');
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return redirect()->route('campaigns.index')->with('success', 'Campaign deleted successfully.');
    }
}
