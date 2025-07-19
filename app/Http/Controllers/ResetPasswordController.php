<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    /**
     * Reset Password
     *
     * Resets the user's password using the reset token.
     *
     * @bodyParam token string required The password reset token.
     * @bodyParam email string required The email address of the user.
     * @bodyParam password string required The new password.
     * @bodyParam password_confirmation string required Password confirmation.
     *
     * @response 200 { "message": "Password has been reset successfully." }
     * @response 422 { "message": "This password reset token is invalid." }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password has been reset successfully.',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}