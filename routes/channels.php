<?php

use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chat}', function (User $user, Chat $chat) {
    return true;
    return $chat->user_one_id === $user->id || $chat->user_two_id === $user->id;
});
