<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountStatus;
use App\Enums\StaffRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStaffAccountRequest;
use App\Http\Requests\ModifyStaffAccountRequest;
use App\Http\Requests\UpdateStaffStatusRequest;
use App\Models\Staff;
use App\Services\StaffManagementService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles staff account management for administrators.
 *
 * This controller manages all staff account operations including
 * listing, creating, editing, updating, and status management.
 * All methods require administrator privileges.
 */
class StaffManagementController extends Controller
{
    public function __construct(private StaffManagementService $staffManagementService) {}

    // Render staff account list inside the staff dashboard
    public function index(): Response
    {
        $accounts = $this->staffManagementService->getAllStaff();

        return Inertia::render('Staff/StaffDashboard', [
            'tab' => 'management',
            'accounts' => $accounts,
        ]);
    }

    // Create new staff account and redirect to account list
    public function store(CreateStaffAccountRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->staffManagementService->createStaffAccount(
            $validated['username'],
            $validated['password'],
            StaffRole::from($validated['role']),
            $validated['full_name'] ?? null,
        );

        return redirect()->route('staff.management')
            ->with('success', 'Staff account created successfully.');
    }

    // Update staff account details and redirect to account list
    public function update(ModifyStaffAccountRequest $request, Staff $account): RedirectResponse
    {
        $this->authorize('update', $account);

        $validated = $request->validated();

        $this->staffManagementService->updateStaffAccount(
            $account->staff_id,
            $validated['full_name'] ?? null,
            $validated['password'] ?? null,
        );

        return redirect()->route('staff.management')
            ->with('success', 'Staff account updated successfully.');
    }

    // Update staff account active/inactive status and redirect back
    public function updateStatus(UpdateStaffStatusRequest $request, Staff $account): RedirectResponse
    {
        $this->authorize('updateStatus', $account);

        $validated = $request->validated();
        $actingAdmin = auth('staff')->user();

        $this->staffManagementService->updateStaffStatus(
            $actingAdmin->staff_id,
            $account->staff_id,
            AccountStatus::from($validated['account_status']),
        );

        return redirect()->route('staff.management')
            ->with('success', 'Account status updated.');
    }
}