<?php

use Illuminate\Support\Facades\Route;

Route::get('/password/reset/{token}', function ($token) {
    return response()->json([
        'message' => 'Use this token with your Flutter app',
        'token' => $token,
        'instructions' => 'Send a POST request to /api/auth/reset-password with this token, email, password, and password_confirmation'
    ]);
})->name('password.reset');
