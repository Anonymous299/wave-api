<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreMessageController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id' => 'required|uuid|exists:chats,id',
            'body' => 'required|string|',
        ]);

        /* @var User $user */
        $user = auth()->user();
        $chat = $user->chats()->findOrFail($request->input('chat_id'));
        $chat->messages()->create([
            'sender_id' => $user->getKey(),
            'body' => $request->input('body'),
        ]);

        return response()->json(
            ['message' => 'Message stored successfully.'],
            201
        );
    }
}
