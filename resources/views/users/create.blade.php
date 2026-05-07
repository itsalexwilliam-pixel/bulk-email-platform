@extends('layouts.app')

@section('page_title', 'Create User')

@section('content')
<div class="p-6 lg:p-8">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Create New User</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Add a new account and assign a role.</p>
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm p-6 sm:p-8">
            <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Name <span class="text-rose-500">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                        class="mt-1.5 block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')
                        <p class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Email <span class="text-rose-500">*</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                        class="mt-1.5 block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('email')
                        <p class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Role <span class="text-rose-500">*</span></label>
                    <select id="role" name="role" required
                        class="mt-1.5 block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select role</option>
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                        <option value="manager" @selected(old('role') === 'manager')>Manager</option>
                        <option value="operator" @selected(old('role') === 'operator')>Operator</option>
                    </select>
                    @error('role')
                        <p class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Password <span class="text-rose-500">*</span></label>
                        <input id="password" name="password" type="password" required
                            class="mt-1.5 block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('password')
                            <p class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Confirm Password <span class="text-rose-500">*</span></label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            class="mt-1.5 block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('users.index') }}"
                       class="inline-flex items-center rounded-lg border border-slate-300 dark:border-slate-700 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition">
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
