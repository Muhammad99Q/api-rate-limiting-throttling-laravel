<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// =====================================================
// UNPROTECTED ROUTE (No rate limiting)
// Used as a "Before" baseline to show unlimited access.
// =====================================================
Route::get('/products-unprotected', [ProductController::class, 'unprotected']);

// =====================================================
// PROTECTED ROUTE (Dynamic rate limiting via 'tiered-api')
// Guest = 3/min, Normal User = 6/min, VIP = 20/min.
// =====================================================
Route::get('/products-protected', [ProductController::class, 'protected'])
    ->middleware('throttle:tiered-api');

// =====================================================
// LOGIN ROUTE (Brute-force protection via 'login-attempts')
// Only 3 login attempts per minute per email+IP combination.
// =====================================================
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login-attempts');

// =====================================================
// AUTHENTICATED ROUTE (Requires Sanctum token + rate limiting)
// Returns the currently logged-in user's profile info.
// =====================================================
Route::get('/me', [AuthController::class, 'me'])
    ->middleware(['auth:sanctum', 'throttle:tiered-api']);
