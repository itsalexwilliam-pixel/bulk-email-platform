<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $accountId = (int) ($request->user()?->account_id ?? 0);
        abort_if($accountId <= 0, 403, 'Account context is missing.');

        $groups = Group::query()
            ->where('account_id', $accountId)
            ->withCount('contacts')
            ->orderBy('name')
            ->paginate(10);

        return view('groups.index', compact('groups'));
    }

    public function store(Request $request)
    {
        $accountId = (int) ($request->user()?->account_id ?? 0);
        abort_if($accountId <= 0, 403, 'Account context is missing.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Group::create([
            'account_id' => $accountId,
            'name' => $data['name'],
        ]);

        return redirect()->route('groups.index')->with('success', 'Group created successfully.');
    }

    public function destroy(Request $request, Group $group)
    {
        $accountId = (int) ($request->user()?->account_id ?? 0);
        abort_if($accountId <= 0, 403, 'Account context is missing.');
        abort_if((int) $group->account_id !== $accountId, 403, 'Forbidden');

        $group->delete();

        return redirect()->route('groups.index')->with('success', 'Group deleted successfully.');
    }
}
