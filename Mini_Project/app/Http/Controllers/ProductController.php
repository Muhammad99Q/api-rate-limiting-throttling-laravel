<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Unprotected endpoint - returns all products with NO rate limiting.
     * Used as a "Before" baseline to show what happens without throttling.
     */
    public function unprotected()
    {
        // Simply return all products (no throttle middleware)
        return response()->json(Product::all());
    }

    /**
     * Protected endpoint - returns all products WITH dynamic rate limiting.
     * The 'tiered-api' limiter applies different limits based on user role:
     *   Guest (no token) = 3/min, User = 6/min, VIP = 20/min
     */
    public function protected()
    {
        // Same data, but rate-limited by the 'tiered-api' middleware
        return response()->json(Product::all());
    }
}
