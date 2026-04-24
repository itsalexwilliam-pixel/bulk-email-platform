<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::with('groups')->latest()->paginate(10);
        return view('contacts.index', compact('contacts'));
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
            'email' => ['required', 'email', 'max:255', 'unique:contacts,email'],
            'groups' => ['nullable', 'array'],
            'groups.*' => ['exists:groups,id'],
        ]);

        $contact = Contact::create([
            'name' => $data['name'],
            'email' => $data['email'],
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
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('contacts', 'email')->ignore($contact->id),
            ],
            'groups' => ['nullable', 'array'],
            'groups.*' => ['exists:groups,id'],
        ]);

        $contact->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $contact->groups()->sync($data['groups'] ?? []);

        return redirect()->route('contacts.index')->with('success', 'Contact updated successfully.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('contacts.index')->with('success', 'Contact deleted successfully.');
    }
}
