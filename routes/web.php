<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

require __DIR__.'/public.php';
require __DIR__.'/customer.php';
require __DIR__.'/staff.php';
require __DIR__.'/admin.php';

Route::fallback(fn () => Inertia::render('NotFound'));