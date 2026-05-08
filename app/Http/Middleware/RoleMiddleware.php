<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role hierarchy: admin > manager > operator
 *
 * Usage in routes:  ->middleware('role:manager')
 * Meaning: user must have at least "manager" level (manager or admin).
 */
class RoleMiddleware
{
    private const HIERARCHY = [
        'operator' => 1,
        'manager'  => 2,
        'admin'    => 3,
    ];

    public function handle(Request $request, Closure $next, string $minRole = 'operator'): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Forbidden.');
        }

        $userLevel = self::HIERARCHY[$user->role] ?? 0;
        $requiredLevel = self::HIERARCHY[$minRole] ?? 99;

        if ($userLevel < $requiredLevel) {
            if ($request->expectsJson()) {
                abort(403, 'You do not have permission to perform this action.');
            }

            return redirect()->route('dashboard')->with(
                'error',
                'You do not have permission to access that page.'
            );
        }

        return $next($request);
    }
}
