<?php

use App\Http\Controllers\GetNearbyUsers;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('nearby', GetNearbyUsers::class)->name('nearby');
        Route::get('me', function () {
            return response()->json(auth()->user());
        })->name('me');
    });
});

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register', RegisterController::class)->name('register');
    Route::post('login', LoginController::class)->name('login');
});
