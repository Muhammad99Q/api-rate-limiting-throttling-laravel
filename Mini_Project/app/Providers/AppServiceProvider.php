<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 1. Dynamic Rate Limiter: Guest (3/min), User (6/min), VIP (20/min)
        RateLimiter::for('tiered-api', function (Request $request) {
            // Get logged-in user from Bearer token (returns null for Guest)
            $user = $request->user('sanctum');

            // Default values for Guest
            $tier = 'guest';
            $limit = 3;
            $key = $request->ip();

            // Change limit and key if user is logged in
            if ($user && $user->role === 'vip') {
                $tier = 'vip';
                $limit = 20;
                $key = $user->id;
            } elseif ($user && $user->role === 'user') {
                $tier = 'user';
                $limit = 6;
                $key = $user->id;
            }

            // Enforce the limit and return 429 JSON if exceeded
            return Limit::perMinute($limit)
                ->by($key)
                ->response(function (Request $request, array $headers) use ($tier) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Rate limit exceeded. Please slow down.',
                        'tier' => $tier,
                        'retry_after_seconds' => $headers['Retry-After'] ?? 60,
                    ], 429, $headers);
                });
        });

        // 2. Login Brute-Force Protection: max 3 attempts per minute
        RateLimiter::for('login-attempts', function (Request $request) {
            // Track by email + IP address
            $email = strtolower((string) $request->input('email', ''));
            $key = $email . '|' . $request->ip();

            return Limit::perMinute(3)
                ->by($key)
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'status' => 'blocked',
                        'message' => 'Too many login attempts. Account temporarily throttled.',
                        'retry_after_seconds' => $headers['Retry-After'] ?? 60,
                    ], 429, $headers);
                });
        });
    }
}
