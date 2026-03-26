<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures only one authentication guard is active in a web session.
 *
 * The currently active guard is tracked in session under "active_guard".
 * If both guards are authenticated, the non-active guard is logged out.
 */
class EnforceSingleGuardSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $hasCustomer = Auth::guard('customer')->check();
        $hasStaff = Auth::guard('staff')->check();
        $activeGuard = $request->session()->get('active_guard');

        if ($hasCustomer && $hasStaff) {
            if ($activeGuard === 'staff') {
                Auth::guard('customer')->logout();
            } else {
                // Default to customer when the session marker is absent/invalid.
                Auth::guard('staff')->logout();
                $request->session()->put('active_guard', 'customer');

                if ($request->is('staff') || $request->is('staff/*')) {
                    return redirect()->route('account.profile');
                }
            }
        } elseif ($hasCustomer) {
            $request->session()->put('active_guard', 'customer');
        } elseif ($hasStaff) {
            $request->session()->put('active_guard', 'staff');
        } else {
            $request->session()->forget('active_guard');
        }

        $response = $next($request);

        if ($response instanceof RedirectResponse && $request->session()->has('active_guard')) {
            $response->headers->set('Vary', 'Cookie');
        }

        return $response;
    }
}
