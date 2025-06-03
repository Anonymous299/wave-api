<?php

use App\Http\Controllers\ChangeUserLocationController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GetNearbyUserCount;
use App\Http\Controllers\GetNearbyUsers;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\StoreMessageController;
use App\Http\Controllers\StoreSwipeController;
use App\Http\Controllers\UpdateUserBioController;
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
        Route::get('nearby/count', GetNearbyUserCount::class)->name('nearby.count');

        Route::get('me', fn() => response()->json(auth()->user()))->name('me');
        Route::post('me', UpdateUserBioController::class);

        Route::post('location', ChangeUserLocationController::class)
            ->name('location.update');

    });

    Route::prefix('swipes')->name('swipes.')->group(function () {
        Route::post('/', StoreSwipeController::class)->name('store');
    });

    Route::prefix('messages')->name('messages.')->group(function () {
        Route::post('/', StoreMessageController::class)->name('store');
    });

    Route::prefix('chats')->name('chats.')->group(function () {
        Route::get('/', [ChatController::class, 'index'])
            ->name('index');
        Route::get('/{chat}', [ChatController::class, 'get'])
            ->name('get');
        Route::get('/{chat}/messages', [ChatController::class, 'messages'])
            ->name('messages');
    });

    Route::get('/fcm-test', function(Illuminate\Http\Request $request) {
        auth()->user()->notify(new \App\Notifications\MessageReceived());

        return response()->json(['status' => 'ok']);
    });
});

Route::prefix('auth')->name('auth.')->group(function () {

    Route::post('register', RegisterController::class)->name('register');
    Route::post('login', LoginController::class)->name('login');
});
