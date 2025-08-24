<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * @group Authentication
     *
     * @unauthenticated
     *
     * POST /api/login
     *
     * @bodyParam email string required Example: test@example.com
     * @bodyParam password string required Example: password
     *
     * @response 200 {
     *   "message": "Login successful",
     *   "access_token": "token_here",
     *   "token_type": "Bearer"
     * }
     * @response 401 {
     *   "message": "Invalid credentials"
     * }
     */
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

    /**
     * @group Authentication
     *
     * @authenticated
     *
     * GET /api/logout
     *
     * @response 200 {
     *   "message": "Logged out successfully"
     * }
     * @response 401 {
     *   "message": "unauthenticated"
     * }
     */
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
