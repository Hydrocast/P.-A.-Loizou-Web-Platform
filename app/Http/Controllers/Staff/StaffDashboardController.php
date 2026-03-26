<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class StaffDashboardController extends Controller
{
    /**
     * Redirect staff users to the main working section after login.
     *
     * The /staff/dashboard route is kept as a stable post-login entry point
     * because authentication flows may already redirect here. Instead of
     * rendering a placeholder dashboard screen, staff are sent directly to
     * order management.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('staff.orders.index');
    }
}