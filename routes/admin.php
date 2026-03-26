<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\PricingConfigurationController;
use App\Http\Controllers\Admin\StaffManagementController;
use App\Http\Controllers\Admin\StaffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Administrator Routes
|--------------------------------------------------------------------------
|
| These routes are for administrators only.
| All routes are prefixed with /staff and use staff.admin middleware.
|
| Middleware used:
|   auth:staff        – require staff authentication.
|   staff.active      – verify account is active on every request.
|   staff.admin       – restrict to administrator role.
|
*/

Route::prefix('staff')
    ->middleware(['auth:staff', 'staff.active', 'staff.admin'])
    ->group(function () {
        // Staff account management
        Route::get('/management', [StaffManagementController::class, 'index'])->name('staff.management');
        Route::get('/accounts', [StaffManagementController::class, 'index'])->name('staff.accounts.index');
        Route::post('/accounts', [StaffManagementController::class, 'store'])->name('staff.accounts.store');
        Route::put('/accounts/{account}', [StaffManagementController::class, 'update'])->name('staff.accounts.update');
        Route::patch('/accounts/{account}/status', [StaffManagementController::class, 'updateStatus'])->name('staff.accounts.status.update');

        // Pricing configuration
        Route::get('/pricing', [PricingConfigurationController::class, 'index'])->name('staff.pricing.index');
        Route::put('/pricing/{id}', [PricingConfigurationController::class, 'update'])
            ->whereNumber('id')
            ->name('staff.pricing.update');

        // Sales analytics
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('staff.analytics');
    });