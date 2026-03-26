<?php

use App\Http\Middleware\EnsureCustomerAccountIsActive;
use App\Http\Middleware\EnforceSingleGuardSession;
use App\Http\Middleware\EnsureStaffAccountIsActive;
use App\Http\Middleware\EnsureStaffIsAdministrator;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            EnforceSingleGuardSession::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Redirect unauthenticated users to the correct guard login page.
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('staff') || $request->is('staff/*')) {
                return route('staff.login');
            }

            return route('login');
        });

        // Redirect already-authenticated users away from guest-only pages.
        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->user('staff') !== null) {
                return route('staff.dashboard');
            }

            if ($request->user('customer') !== null) {
                return route('home');
            }

            return route('home');
        });

        $middleware->alias([
            'customer.active' => EnsureCustomerAccountIsActive::class,
            'staff.active' => EnsureStaffAccountIsActive::class,
            'staff.admin' => EnsureStaffIsAdministrator::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();