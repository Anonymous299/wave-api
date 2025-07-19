<?php

use App\Http\Controllers\BlockUserController;
use App\Http\Controllers\ChangeUserLocationController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DeleteUserController;
use App\Http\Controllers\GetNearbyUserCountController;
use App\Http\Controllers\GetNearbyUsersController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\StoreMessageController;
use App\Http\Controllers\StoreSwipeController;
use App\Http\Controllers\UnblockUserController;
use App\Http\Controllers\UpdateUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShowUserController;

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

        Route::get('user', ShowUserController::class)->name('user');
        Route::delete('/', DeleteUserController::class)->name('delete');

        Route::post('block', BlockUserController::class)->name('block');
        Route::delete('unblock', UnblockUserController::class)->name('unblock');
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

    Route::get('/fcm-test', function (Illuminate\Http\Request $request) {
        auth()->user()->notify(new \App\Notifications\MatchCreated(\App\Models\User::query()->firstOrFail(), \App\Models\Chat::query()->firstOrFail()));

        return response()->json(['status' => 'ok']);
    });

    Route::get('/fcm-test-waved', function (Illuminate\Http\Request $request) {
        auth()->user()->notify(new \App\Notifications\UserWaved(\App\Models\User::query()->firstOrFail()));

        return response()->json(['status' => 'ok']);
    });

    Route::get('message-test', function (Illuminate\Http\Request $request) {
        $chatId = $request->input('chat_id');

        $user = auth()->user();

        $message = \App\Models\Message::query()->create([
            'chat_id'   => $chatId,
            'sender_id' => $user->getKey(),
            'body'      => "this message was sent by the shadow wizard money gang\nwe love casting spells"
        ]);

        \App\Events\MessageSent::dispatch($message);

        return response()->json(['status' => 'ok']);
    });

    Route::post('/broadcasting/auth', [\Illuminate\Broadcasting\BroadcastController::class, 'authenticate'])
        ->middleware('auth:sanctum');

});

Route::prefix('auth')->name('auth.')->group(function () {

    Route::post('register', RegisterController::class)->name('register');
    Route::post('login', LoginController::class)->name('login');
    Route::post('forgot-password', ForgotPasswordController::class)->name('forgot-password');
    Route::post('reset-password', ResetPasswordController::class)->name('reset-password');

    Route::get('/password/reset/{token}', function ($token) {
    $email = request()->get('email');
    
    // Redirect to your app's web page with token and email as query parameters
    $resetUrl = "https://waveconnect.app/reset-password?token=" . urlencode($token) . "&email=" . urlencode($email);
    
    return redirect($resetUrl);
})->name('password.reset');
});
