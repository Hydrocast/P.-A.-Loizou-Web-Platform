<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated customer account remains active on every request.
 *
 * If the account has been deactivated, the customer is logged out,
 * the session is invalidated, and they are redirected to the customer login page.
 *
 * This middleware must be applied after 'auth:customer'.
 */
class EnsureCustomerAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $customer = Auth::guard('customer')->user();

        if ($customer !== null && !$customer->isActive()) {
            Auth::guard('customer')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->inertia()) {
                return response()->json(['message' => 'Your account has been deactivated.'], 403);
            }

            return redirect()
                ->route('login')
                ->with('error', 'Your account has been deactivated. Please contact us if you believe this is an error.');
        }

        return $next($request);
    }
}