<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            'name' => config('app.name'),

            'auth' => [
                'customer' => $request->user('customer')
                    ? [
                        'customer_id' => $request->user('customer')->customer_id,
                        'full_name' => $request->user('customer')->full_name,
                        'email' => $request->user('customer')->email,
                    ]
                    : null,

                'staff' => $request->user('staff')
                    ? [
                        'staff_id' => $request->user('staff')->staff_id,
                        'username' => $request->user('staff')->username,
                        'full_name' => $request->user('staff')->full_name,
                        'role' => $request->user('staff')->role,
                    ]
                    : null,
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'status' => fn () => $request->session()->get('status'),
            ],

            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}