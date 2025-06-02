<?php

use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chat}', function (User $user, Chat $chat) {
    return $chat->user_one_id === $user->id || $chat->user_two_id === $user->id;
});
