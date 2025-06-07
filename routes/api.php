<?php

use App\Http\Controllers\ChangeUserLocationController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GetNearbyUserCountController;
use App\Http\Controllers\GetNearbyUsersController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\StoreMessageController;
use App\Http\Controllers\StoreSwipeController;
use App\Http\Controllers\UpdateUserController;
use App\Http\Controllers\UpdateFcmTokenController;
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
        Route::get('nearby', GetNearbyUsersController::class)->name('nearby');
        Route::get('nearby/count', GetNearbyUserCountController::class)->name('nearby.count');

        Route::get('me', fn() => response()->json(auth()->user()))->name('me');
        Route::post('me', UpdateUserController::class);

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
        auth()->user()->notify(new \App\Notifications\MatchCreated(\App\Models\User::firstOrFail()));

        return response()->json(['status' => 'ok']);
    });

    Route::post('/broadcasting/auth', [\Illuminate\Broadcasting\BroadcastController::class, 'authenticate'])
        ->middleware('auth:sanctum'); // or any guard that uses bearer tokens

});

Route::prefix('auth')->name('auth.')->group(function () {

    Route::post('register', RegisterController::class)->name('register');
    Route::post('login', LoginController::class)->name('login');
});
