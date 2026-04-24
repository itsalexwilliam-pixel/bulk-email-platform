<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Unsubscribe;
use Illuminate\Http\Request;

class UnsubscribeController extends Controller
{
    public function unsubscribe(string $email)
    {
        $decoded = urldecode($email);
        $normalized = strtolower(trim($decoded));

        if (!filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            return response()->view('unsubscribe.message', [
                'message' => 'Your unsubscribe request could not be processed. Please check the link.',
            ], 200);
        }

        $contact = Contact::whereRaw('LOWER(email) = ?', [$normalized])->first();

        Unsubscribe::updateOrCreate(
            ['email' => $normalized],
            [
                'contact_id' => $contact?->id,
                'unsubscribed_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->view('unsubscribe.message', [
            'message' => 'You have been unsubscribed successfully.',
        ], 200);
    }

    public function index()
    {
        $unsubscribes = Unsubscribe::with('contact')
            ->orderByDesc('unsubscribed_at')
            ->paginate(25);

        return view('unsubscribe.index', compact('unsubscribes'));
    }
}
