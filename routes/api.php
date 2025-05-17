<?php

use App\Http\Controllers\ChangeUserLocationController;
use App\Http\Controllers\GetNearbyUsers;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\StoreSwipeController;
use Illuminate\Support\Facades\Route;

/**
 * Health
 *
 * Checks that the API is up and running.
 * @response 200 scenario="API is up and running" { "status": "ok" }
 */
Route::get('health', fn() => response()->json(['status' => 'ok']))->name('health');

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('nearby', GetNearbyUsers::class)->name('nearby');
        Route::get('me', fn() => response()->json(auth()->user()))->name('me');

        Route::post('location', ChangeUserLocationController::class)
            ->name('location.update');
    });

    Route::prefix('swipes')->name('swipes.')->group(function () {
        Route::post('/', StoreSwipeController::class)->name('store');
    });
});

Route::prefix('auth')->name('auth.')->group(function () {

    Route::post('register', RegisterController::class)->name('register');
    Route::post('login', LoginController::class)->name('login');
});
