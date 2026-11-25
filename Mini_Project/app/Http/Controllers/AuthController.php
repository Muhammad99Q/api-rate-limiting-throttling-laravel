<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login endpoint - validates credentials and returns a Sanctum API token.
     * Protected by 'login-attempts' rate limiter (3 attempts/min per email+IP).
     */
    public function login(Request $request)
    {
        // Validate that email and password are provided
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and password is correct
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Create a new Sanctum API token for the user
        $token = $user->createToken('api-token')->plainTextToken;

        // Return the token and user info
        return response()->json([
            'status' => 'success',
            'token'  => $token,
            'user'   => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    /**
     * Me endpoint - returns the currently authenticated user's details.
     * Protected by 'auth:sanctum' and 'tiered-api' rate limiter.
     */
    public function me(Request $request)
    {
        // Get the authenticated user from the Sanctum token
        $user = $request->user();

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ]);
    }
}
