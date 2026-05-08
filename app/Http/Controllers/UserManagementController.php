<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(): View
    {
        $accountId = $this->currentAccountId();
        $users = User::where('account_id', $accountId)->orderBy('id', 'desc')->paginate(20);

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->withMessage('This email address is already registered. Each user must have a unique email.')],
            'role' => ['required', Rule::in(['admin', 'manager', 'operator'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'role'       => $validated['role'],
            'password'   => Hash::make($validated['password']),
            'account_id' => $request->user()->account_id,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'manager', 'operator'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($request->user()->id === $user->id && $user->role === 'admin' && $validated['role'] !== 'admin') {
            return back()->withErrors([
                'role' => 'You cannot remove your own admin role.',
            ])->withInput();
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Delete the specified user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Bulk delete users.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['sometimes', 'array'],
            'ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'delete_all' => ['sometimes', 'boolean'],
        ]);

        $deleteAll = (bool) ($data['delete_all'] ?? false);
        $ids = $data['ids'] ?? [];
        $currentUserId = $request->user()->id;
        $accountId = $this->currentAccountId();

        if ($deleteAll) {
            $validIds = User::where('account_id', $accountId)->where('id', '!=', $currentUserId)->pluck('id')->toArray();
        } else {
            $validIds = User::whereIn('id', $ids)
                ->where('account_id', $accountId)
                ->where('id', '!=', $currentUserId)
                ->pluck('id')
                ->toArray();
        }

        if (count($validIds) === 0) {
            return back()->withErrors(['ids' => 'No valid users selected for deletion.']);
        }

        $deleted = User::whereIn('id', $validIds)->delete();

        return back()->with('success', "{$deleted} user(s) deleted successfully.");
    }
}
