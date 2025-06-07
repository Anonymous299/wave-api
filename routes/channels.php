<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chat}', function ($user, $chatId) {
    $chat = Chat::query()->findOrFail($chatId);

    return $chat->user_one_id === $user->id || $chat->user_two_id === $user->id;
});
