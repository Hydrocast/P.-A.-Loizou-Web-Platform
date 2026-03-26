<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts access to administrator-only routes.
 *
 * This middleware checks that the authenticated staff member has the
 * Administrator role. It must be used after 'auth:staff' in the middleware stack.
 *
 * Non-administrators are redirected to the staff dashboard with an error message.
 */
class EnsureStaffIsAdministrator
{
    public function handle(Request $request, Closure $next): Response
    {
        $staff = Auth::guard('staff')->user();

        if ($staff === null || !$staff->isAdministrator()) {
            if ($request->expectsJson() || $request->inertia()) {
                return response()->json(['message' => 'Administrator privileges are required.'], 403);
            }

            return redirect()
                ->route('staff.dashboard')
                ->with('error', 'Administrator privileges are required to access this area.');
        }

        return $next($request);
    }
}