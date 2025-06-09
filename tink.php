<?php

use App\Models\User;

$user = User::factory()->create();

$chat = \App\Models\Chat::factory()->create([
    'user_one_id' => $user->id,
    'user_two_id' => '01975692-8478-734e-a3c0-42d6069f44c8',
]);

dd($chat);
