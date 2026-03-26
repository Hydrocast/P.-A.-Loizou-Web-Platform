<?php

use App\Http\Controllers\Staff\CarouselManagementController;
use App\Http\Controllers\Staff\CategoryManagementController;
use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\Staff\OrderManagementController;
use App\Http\Controllers\Staff\ProductManagementController;
use App\Http\Controllers\Staff\StaffAuthController;
use App\Http\Middleware\EnsureStaffAccountIsActive;
use Illuminate\Support\Facades\Route;

Route::prefix('staff')->group(function () {
    Route::middleware('guest:staff')->group(function () {
        Route::get('/login', [StaffAuthController::class, 'showLogin'])->name('staff.login');
        Route::post('/login', [StaffAuthController::class, 'login'])->name('staff.login.submit');
    });

    Route::middleware(['auth:staff', EnsureStaffAccountIsActive::class])->group(function () {
        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('staff.dashboard');
        Route::post('/logout', [StaffAuthController::class, 'logout'])->name('staff.logout');

        Route::prefix('orders')->name('staff.orders.')->group(function () {
            Route::get('/', [OrderManagementController::class, 'index'])->name('index');
            Route::get('/{order}', [OrderManagementController::class, 'show'])->name('show');
            Route::put('/{order}/status', [OrderManagementController::class, 'updateStatus'])->name('status.update');
            Route::post('/{order}/pickup-email', [OrderManagementController::class, 'sendPickupEmail'])->name('pickup-email.send');
            Route::post('/{order}/notes', [OrderManagementController::class, 'storeNote'])->name('notes.store');
            Route::put('/{order}/reassign', [OrderManagementController::class, 'reassign'])->name('reassign');
        });

        Route::prefix('products')->name('staff.products.')->group(function () {
            Route::get('/', [ProductManagementController::class, 'index'])->name('index');
            Route::post('/', [ProductManagementController::class, 'store'])->name('store');
            Route::put('/{type}/{id}', [ProductManagementController::class, 'update'])->name('update');
        });

        Route::prefix('categories')->name('staff.categories.')->group(function () {
            Route::get('/', [CategoryManagementController::class, 'index'])->name('index');
            Route::post('/', [CategoryManagementController::class, 'store'])->name('store');
            Route::put('/{category}', [CategoryManagementController::class, 'update'])->name('update');
            Route::delete('/{category}', [CategoryManagementController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('carousel')->name('staff.carousel.')->group(function () {
            Route::get('/', [CarouselManagementController::class, 'index'])->name('index');
            Route::post('/', [CarouselManagementController::class, 'store'])->name('store');
            Route::put('/reorder', [CarouselManagementController::class, 'reorder'])->name('reorder');
            Route::put('/{slide}', [CarouselManagementController::class, 'update'])->name('update');
            Route::delete('/{slide}', [CarouselManagementController::class, 'destroy'])->name('destroy');
        });
    });

    Route::redirect('/', '/staff/orders');
});