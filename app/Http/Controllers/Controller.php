<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Resolve the current user's account ID from the request.
     * Aborts with 403 if the account context is missing.
     */
    protected function getAccountId(Request $request): int
    {
        $accountId = (int) ($request->user()?->account_id ?? 0);
        abort_if($accountId <= 0, 403, 'Account context is missing.');

        return $accountId;
    }

    /**
     * Resolve account ID from the authenticated user directly (no Request needed).
     * Useful in controllers that don't have a Request parameter in scope.
     */
    protected function currentAccountId(): int
    {
        $accountId = (int) (auth()->user()?->account_id ?? 0);
        abort_if($accountId <= 0, 403, 'Account context is missing.');

        return $accountId;
    }
}
