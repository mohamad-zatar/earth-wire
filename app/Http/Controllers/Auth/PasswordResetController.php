<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Jobs\SendResetPasswordEmail;
use App\Models\User;
use Carbon\Carbon;
/**
 * @group Password Reset
 * APIs for managing password resets.
 */
class PasswordResetController extends Controller
{
    /**
     * Request a password reset code
     *
     * This endpoint allows a user to request a password reset code by providing their registered email address.
     * The reset code is sent via email and expires in 10 minutes.
     *
     * @unauthenticated
     * This endpoint does not require authentication.
     *
     * @bodyParam email string required The email address of the user. Example: john.doe@example.com
     *
     * @response 200 {
     *   "message": "Password reset code sent to your email."
     * }
     *
     * @response 404 {
     *   "message": "User with the given email does not exist."
     * }
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        $resetCode = rand(100000, 999999);
        $user->update([
            'password_reset_code' => $resetCode,
            'password_reset_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        SendResetPasswordEmail::dispatch($user, $resetCode);

        return response()->json(['message' => 'Password reset code sent to your email.']);
    }

    /**
     * Reset a user's password
     *
     * This endpoint allows a user to reset their password using the reset code they received via email.
     *
     * @unauthenticated
     * This endpoint does not require authentication.
     *
     * @bodyParam email string required The email address of the user. Example: john.doe@example.com
     * @bodyParam code integer required The password reset code sent to the user's email. Example: 123456
     * @bodyParam password string required The new password for the account. Example: newpassword123
     * @bodyParam password_confirmation string required The new password confirmation. Example: newpassword123
     *
     * @response 200 {
     *   "message": "Password has been reset successfully."
     * }
     *
     * @response 400 {
     *   "message": "Invalid reset code."
     * }
     *
     * @response 400 {
     *   "message": "Reset code has expired."
     * }
     */

    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || $user->password_reset_code !== $request->code) {
            return response()->json(['message' => 'Invalid reset code.'], 400);
        }

        if (Carbon::now()->greaterThan($user->password_reset_expires_at)) {
            return response()->json(['message' => 'Reset code has expired.'], 400);
        }

        $user->update([
            'password' => bcrypt($request->password),
            'password_reset_code' => null,
            'password_reset_expires_at' => null,
        ]);

        return response()->json(['message' => 'Password has been reset successfully.']);
    }
}
