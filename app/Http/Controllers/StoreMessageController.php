<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoreMessageController extends Controller
{
    /**
     * Send a Message
     *
     * Stores a new message in the given chat. The authenticated user must be a participant in the chat.
     *
     * @authenticated
     *
     * @bodyParam chat_id uuid required The UUID of the chat. Example: 7d9b8e3a-1e24-4a6f-90a1-2e7b2cb80d1b
     * @bodyParam body string required The message body. Example: Hello there
     *
     * @response 201 {
     *   "message": "Message stored successfully."
     * }
     *
     * @response 404 {
     *   "message": "No query results for model [App\\Models\\Chat]..."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "chat_id": ["The chat_id field is required."],
     *     "body": ["The body field is required."]
     *   }
     * }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id' => 'required|uuid|exists:chats,id',
            'body'    => 'required|string|',
        ]);

        /* @var User $user */
        $user = auth()->user();
        $chat = Chat::query()->findOrFail($request->get('chat_id'));

        if (
            (string)$chat->user_one_id !== (string)$user->getKey()
            && (string)$chat->user_two_id !== (string)$user->getKey()
        ) {
            return response()->json([], 404);
        }

        $message = $chat->messages()->create([
            'sender_id' => $user->getKey(),
            'body'      => $request->input('body'),
        ]);

        MessageSent::dispatch($message);

        return response()->json(
            ['message' => 'Message stored successfully.'],
            201
        );
    }
}
