<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactTag;
use Illuminate\Http\Request;

class ContactTagController extends Controller
{
    public function store(Request $request, Contact $contact)
    {
        $accountId = $this->getAccountId($request);
        abort_if((int) $contact->account_id !== $accountId, 403);

        $data = $request->validate([
            'tag' => ['required', 'string', 'max:60', 'regex:/^[a-zA-Z0-9 _\-]+$/'],
        ]);

        $tag = trim($data['tag']);

        // Avoid duplicate
        ContactTag::firstOrCreate([
            'contact_id' => $contact->id,
            'account_id' => $accountId,
            'tag'        => $tag,
        ]);

        return back()->with('success', "Tag '{$tag}' added.");
    }

    public function destroy(Request $request, Contact $contact, ContactTag $tag)
    {
        $accountId = $this->getAccountId($request);
        abort_if((int) $contact->account_id !== $accountId, 403);
        abort_if((int) $tag->contact_id !== $contact->id, 403);

        $tag->delete();

        return back()->with('success', 'Tag removed.');
    }
}
