<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Create a new token for the user.
            // The argument 'auth_token' is a name for the token,
            // which can be useful for organization.
            $token = $user->createToken('auth_token');

            return response()->json([
                'message' => 'Login successful',
                // Return the raw, plain-text token to the client.
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
            ]);
        }

        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }

    public function logout(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Delete the current token being used for authentication
        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
}
