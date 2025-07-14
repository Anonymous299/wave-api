<?php

use Illuminate\Support\Facades\Route;

Route::get('/password/reset/{token}', function ($token) {
    $email = request()->get('email');
    
    // Redirect to your app's web page with token and email as query parameters
    $resetUrl = "https://waveconnect.app/reset-password?token=" . urlencode($token) . "&email=" . urlencode($email);
    
    return redirect($resetUrl);
})->name('password.reset');
