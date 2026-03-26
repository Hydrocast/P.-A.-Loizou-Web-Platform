<?php

use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Public\HomepageController;
use App\Http\Controllers\Public\ProductCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomepageController::class, 'index'])->name('home');
Route::get('/about', [HomepageController::class, 'about'])->name('about');
Route::get('/services', [HomepageController::class, 'services'])->name('services');
Route::get('/contact', [HomepageController::class, 'contact'])->name('contact');
Route::post('/contact', [HomepageController::class, 'submitContact'])->name('contact.submit');

Route::get('/catalog', [ProductCatalogController::class, 'index'])->name('catalog');
Route::get('/product/{type}/{id}', [ProductCatalogController::class, 'show'])
    ->whereIn('type', ['standard', 'customizable'])
    ->whereNumber('id')
    ->name('product.show');

Route::middleware('guest:customer')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->name('customer.login.submit');

    Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->name('customer.register');

    Route::get('/forgot-password', [CustomerAuthController::class, 'showForgotPassword'])
       ->name('customer.password.request');

    Route::post('/forgot-password', [CustomerAuthController::class, 'sendPasswordResetEmail'])
       ->name('customer.password.email');

    Route::get('/reset-password/{token}', [CustomerAuthController::class, 'showResetPassword'])
       ->name('customer.password.reset');

    Route::post('/reset-password/{token}', [CustomerAuthController::class, 'resetPassword'])
       ->name('customer.password.update');
});