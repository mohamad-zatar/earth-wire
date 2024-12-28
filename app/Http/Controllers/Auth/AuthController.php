<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @group Authentication
 * APIs for user authentication.
 */
class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * This endpoint allows a new user to register by providing their name, email, and password.
     *
     * @unauthenticated
     * This endpoint does not require authentication.
     *
     * @bodyParam name string required The name of the user. Example: John Doe
     * @bodyParam email string required The email address of the user. Example: john.doe@example.com
     * @bodyParam password string required The password for the user account. Example: secret123
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token]);
    }
    /**
     * Login a user
     *
     * This endpoint allows an existing user to log in by providing their email and password.
     *
     * @unauthenticated
     * This endpoint does not require authentication.
     *
     * @bodyParam email string required The email address of the user. Example: john.doe@example.com
     * @bodyParam password string required The password for the user account. Example: secret123
     *
     * @response 200 {
     *   "access_token": "1|ZiYkWaM67jjYtElLk0m1OMZLGafcrqZMDRoTjpzQd61329be"
     * }
     *
     * @response 401 {
     *   "message": "Invalid login details"
     * }
     */

    public function login(LoginRequest $request)
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token]);
    }
    /**
     * Logout the authenticated user
     *
     * This endpoint allows an authenticated user to log out and revoke their access token.
     *
     * @authenticated
     * This endpoint requires a valid access token.
     *
     * @response 200 {
     *   "message": "Logged out successfully"
     * }
     */

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
