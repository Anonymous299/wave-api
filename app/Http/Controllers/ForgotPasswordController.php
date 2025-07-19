<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Send Password Reset Link
     *
     * Sends a password reset link to the user's email address.
     *
     * @bodyParam email string required The email address of the user.
     *
     * @response 200 { "message": "Password reset link sent to your email address." }
     * @response 422 { "message": "We can't find a user with that email address." }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent to your email address.',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}