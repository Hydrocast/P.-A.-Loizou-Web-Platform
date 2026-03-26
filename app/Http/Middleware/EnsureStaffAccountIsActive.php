<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated staff account remains active on every request.
 *
 * If the account has been deactivated, the staff member is logged out,
 * the session is invalidated, and they are redirected to the staff login page.
 *
 * This middleware must be applied after 'auth:staff'.
 */
class EnsureStaffAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $staff = Auth::guard('staff')->user();

        if ($staff !== null && !$staff->isActive()) {
            Auth::guard('staff')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->inertia()) {
                return response()->json(['message' => 'Your account has been deactivated.'], 403);
            }

            return redirect()
                ->route('staff.login')
                ->with('error', 'Your account has been deactivated. Please contact an administrator.');
        }

        return $next($request);
    }
}