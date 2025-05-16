<?php

use App\Http\Controllers\GetNearbyUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('users')->name('users.')->group(function () {
    Route::get('nearby', GetNearbyUsers::class)->name('nearby');
});
