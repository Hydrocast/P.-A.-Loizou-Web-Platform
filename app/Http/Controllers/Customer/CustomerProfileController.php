<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\CustomerAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CustomerProfileController extends Controller
{
    public function __construct(private CustomerAuthService $customerAuthService) {}

    public function show(): Response
    {
        return Inertia::render('Customer/Account/CustomerProfile', [
            'customer' => Auth::guard('customer')->user(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $customerId = Auth::guard('customer')->id();
        $data = $request->validated();

        $this->customerAuthService->updateProfile(
            $customerId,
            $data['full_name'],
            $data['phone_number'] ?? null
        );

        return redirect()->route('account.profile')
            ->with('success', 'Profile updated successfully.');
    }
}