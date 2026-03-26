<?php

use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\Customer\DesignController;
use App\Http\Controllers\Customer\OrderHistoryController;
use App\Http\Controllers\Customer\WishlistController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:customer', 'customer.active'])->group(function () {
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');

    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::post('/', [WishlistController::class, 'store'])->name('store');
        Route::delete('/{wishlistItem}', [WishlistController::class, 'destroy'])
            ->name('destroy')
            ->where('wishlistItem', '[0-9]+');
    });

    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/', [CartController::class, 'store'])->name('store');
        Route::post('/from-design', [CartController::class, 'addFromDesign'])->name('from-design');
        Route::patch('/{cartItem}', [CartController::class, 'update'])->name('update');
        Route::delete('/{cartItem}', [CartController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'review'])->name('review');
        Route::post('/', [CheckoutController::class, 'submit'])->name('submit');
        Route::get('/confirmation/{order}', [CheckoutController::class, 'confirmation'])
            ->whereNumber('order')
            ->name('confirmation');
    });

    Route::get('/design/{id}', [DesignController::class, 'workspace'])
        ->whereNumber('id')
        ->name('design.workspace');

    Route::post('/design/save', [DesignController::class, 'store'])->name('design.save');
    Route::get('/designs/{design}/load', [DesignController::class, 'load'])->name('designs.load');
    Route::delete('/designs/{design}', [DesignController::class, 'destroy'])->name('designs.destroy');

    Route::redirect('/account', '/account/profile');

    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/profile', [CustomerProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [CustomerProfileController::class, 'update'])->name('profile.update');

        Route::get('/orders', [OrderHistoryController::class, 'index'])->name('orders');
        Route::get('/designs', [DesignController::class, 'index'])->name('designs');
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    });
});