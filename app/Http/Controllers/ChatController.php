<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatResource;
use App\Models\Chat;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request): ChatResource
    {
        $request->validate([
            'chat_id' => 'required|uuid',
        ]);

        return new ChatResource(Chat::query()->findOrFail($request->input('chat_id')));
    }
}
