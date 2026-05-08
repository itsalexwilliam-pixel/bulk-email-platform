<?php

namespace App\Http\Controllers;

use App\Models\SuppressionEntry;
use Illuminate\Http\Request;

class SuppressionController extends Controller
{
    public function index()
    {
        $accountId = $this->currentAccountId();

        $entries = SuppressionEntry::where('account_id', $accountId)
            ->latest()
            ->paginate(25);

        return view('suppression.index', compact('entries'));
    }

    public function store(Request $request)
    {
        $accountId = $this->getAccountId($request);

        $data = $request->validate([
            'email'  => ['required', 'email', 'max:255'],
            'reason' => ['nullable', 'string', 'max:100'],
        ]);

        SuppressionEntry::updateOrCreate(
            ['account_id' => $accountId, 'email' => strtolower(trim($data['email']))],
            ['reason' => $data['reason'] ?? 'manual']
        );

        return back()->with('success', "{$data['email']} added to suppression list.");
    }

    public function destroy(SuppressionEntry $suppression)
    {
        $accountId = $this->currentAccountId();
        abort_if((int) $suppression->account_id !== $accountId, 403);

        $suppression->delete();

        return back()->with('success', 'Email removed from suppression list.');
    }

    public function bulkImport(Request $request)
    {
        $accountId = $this->getAccountId($request);

        $data = $request->validate([
            'emails' => ['required', 'string'],
        ]);

        $lines = preg_split('/[\r\n,]+/', $data['emails']);
        $added = 0;

        foreach ($lines as $line) {
            $email = strtolower(trim($line));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;

            SuppressionEntry::updateOrCreate(
                ['account_id' => $accountId, 'email' => $email],
                ['reason' => 'bulk_import']
            );
            $added++;
        }

        return back()->with('success', "{$added} email(s) added to suppression list.");
    }
}
