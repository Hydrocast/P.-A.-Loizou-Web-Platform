<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginCustomerRequest;
use App\Http\Requests\RegisterCustomerRequest;
use App\Http\Requests\RequestPasswordResetRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\CustomerAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CustomerAuthController extends Controller
{
    public function __construct(private CustomerAuthService $customerAuthService) {}

    public function showRegister(): Response
    {
        return Inertia::render('Customer/Auth/CustomerRegister');
    }

    public function register(RegisterCustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (Auth::guard('staff')->check()) {
            Auth::guard('staff')->logout();
        }

        $customer = $this->customerAuthService->register(
            $data['email'],
            $data['password'],
            $data['full_name'],
            $data['phone_number'] ?? null,
        );

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();
        $request->session()->put('active_guard', 'customer');

        return redirect()->route('home')
            ->with('success', 'Welcome! Your account has been created.');
    }

    public function showLogin(): Response
    {
        return Inertia::render('Customer/Auth/CustomerLogin');
    }

    public function login(LoginCustomerRequest $request): RedirectResponse
    {
        try {
            $request->ensureIsNotRateLimited();
        } catch (ValidationException $exception) {
            Log::warning('Customer login lockout triggered.', [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'throttle_key' => $request->throttleKey(),
            ]);

            throw $exception;
        }

        $data = $request->validated();

        if (Auth::guard('staff')->check()) {
            Auth::guard('staff')->logout();
        }

        try {
            $customer = $this->customerAuthService->login(
                $data['email'],
                $data['password'],
            );
        } catch (ValidationException $exception) {
            $request->hitRateLimiter();

            Log::warning('Failed customer login attempt.', [
                'email' => $data['email'],
                'ip' => $request->ip(),
                'throttle_key' => $request->throttleKey(),
            ]);

            throw $exception;
        }

        $request->clearRateLimiter();

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();
        $request->session()->put('active_guard', 'customer');

        return redirect()->intended(route('home'));
    }

    public function logout(): RedirectResponse
    {
        $this->customerAuthService->logout();
        request()->session()->forget('active_guard');

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function showForgotPassword(): Response
    {
        return Inertia::render('Customer/Auth/ForgotPassword');
    }

    public function sendPasswordResetEmail(RequestPasswordResetRequest $request): RedirectResponse
    {
        $this->customerAuthService->requestPasswordReset(
            $request->validated()['email']
        );

        return back()->with('status', 'If an account with that email exists, a reset link has been sent.');
    }

    public function showResetPassword(string $token): Response
    {
        return Inertia::render('Customer/Auth/ResetPassword', [
            'token' => $token,
            'email' => request()->query('email', ''),
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->customerAuthService->resetPassword(
            $data['email'],
            $data['token'],
            $data['password'],
        );

        return redirect()->route('login')
            ->with('status', 'Your password has been reset. Please log in.');
    }
}