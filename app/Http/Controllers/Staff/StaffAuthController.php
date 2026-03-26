<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginStaffRequest;
use App\Services\StaffAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles staff authentication.
 *
 * Staff authenticate through the dedicated staff guard and are redirected
 * to the staff dashboard after successful login.
 */
class StaffAuthController extends Controller
{
    public function __construct(private StaffAuthService $staffAuthService) {}

    /**
     * Render the staff login page.
     */
    public function showLogin(): Response
    {
        return Inertia::render('Staff/Auth/StaffLogin');
    }

    /**
     * Authenticate the staff member and start a new session.
     */
    public function login(LoginStaffRequest $request): RedirectResponse
    {
        try {
            $request->ensureIsNotRateLimited();
        } catch (ValidationException $exception) {
            Log::warning('Staff login lockout triggered.', [
                'username' => $request->input('username'),
                'ip' => $request->ip(),
                'throttle_key' => $request->throttleKey(),
            ]);

            throw $exception;
        }

        $data = $request->validated();

        if (Auth::guard('customer')->check()) {
            Auth::guard('customer')->logout();
        }

        try {
            $staff = $this->staffAuthService->login(
                $data['username'],
                $data['password'],
            );
        } catch (ValidationException $exception) {
            $request->hitRateLimiter();

            Log::warning('Failed staff login attempt.', [
                'username' => $data['username'],
                'ip' => $request->ip(),
                'throttle_key' => $request->throttleKey(),
            ]);

            throw $exception;
        }

        $request->clearRateLimiter();

        Auth::guard('staff')->login($staff);
        $request->session()->regenerate();
        $request->session()->put('active_guard', 'staff');

        Log::info('Successful staff login.', [
            'staff_id' => $staff->staff_id,
            'username' => $staff->username,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('staff.dashboard');
    }

    /**
     * Log the staff member out and invalidate the session.
     */
    public function logout(): RedirectResponse
    {
        $this->staffAuthService->logout();
        request()->session()->forget('active_guard');

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('staff.login');
    }
}