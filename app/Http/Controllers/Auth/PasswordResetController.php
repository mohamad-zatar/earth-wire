<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Jobs\SendResetPasswordEmail;
use App\Models\User;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
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
