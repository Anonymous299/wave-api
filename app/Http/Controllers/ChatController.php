<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatResource;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChatController extends Controller
{
    /**
     * Get Chat by Query Parameter
     *
     * Returns a chat resource by its ID passed as a query parameter.
     *
     * @queryParam chat_id uuid required The UUID of the chat. Example: 7d9b8e3a-1e24-4a6f-90a1-2e7b2cb80d1b
     *
     * @response 200 {
     *   "id": "7d9b8e3a-1e24-4a6f-90a1-2e7b2cb80d1b",
     *   "user_one": {
     *     "id": "1a2b3c4d-5e6f-7a8b-9c0d-1e2f3a4b5c6d",
     *     "name": "Alice"
     *   },
     *   "user_two": {
     *     "id": "2b3c4d5e-6f7a-8b9c-0d1e-2f3a4b5c6d7e",
     *     "name": "Bob"
     *   },
     *   "created_at": "2025-06-02T12:00:00Z",
     *   "messages": [
     *     {
     *       "id": "62b8d8e3-4f24-4a1e-b0a1-9e7b2cb81a1b",
     *       "chat_id": "7d9b8e3a-1e24-4a6f-90a1-2e7b2cb80d1b",
     *       "sender_id": "1a2b3c4d-5e6f-7a8b-9c0d-1e2f3a4b5c6d",
     *       "body": "Hey there!",
     *       "created_at": "2025-06-02T12:05:00Z",
     *       "updated_at": "2025-06-02T12:05:00Z"
     *     }
     *   ]
     * }
     */
    public function get(Chat $chat): ChatResource
    {
        return new ChatResource($chat);
    }

    /**
     * List All Chats for Authenticated User
     *
     * Returns a list of all chat records associated with the authenticated user.
     * Each chat includes limited user info and the most recent 5 messages.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": "01973234-02e7-7398-a9fd-2a8b7f2590c1",
     *       "user_one": {
     *         "id": "3c58af0c-9d9b-488a-a607-f52e137a73e8",
     *         "name": "Selmer Ondricka"
     *       },
     *       "user_two": {
     *         "id": "81d1e66e-21b6-4ab7-bf42-7055f55d74ce",
     *         "name": "Ms. Katrine Altenwerth DVM"
     *       },
     *       "created_at": "2025-06-02T19:52:38.000000Z",
     *       "messages": []
     *     },
     *     {
     *       "id": "01973234-02eb-70bc-b1a9-310fb1210b25",
     *       "user_one": {
     *         "id": "688827f9-c5b6-4f7c-94b7-19c91c7295d0",
     *         "name": "Lazaro Lang"
     *       },
     *       "user_two": {
     *         "id": "3c58af0c-9d9b-488a-a607-f52e137a73e8",
     *         "name": "Selmer Ondricka"
     *       },
     *       "created_at": "2025-06-02T19:52:38.000000Z",
     *       "messages": []
     *     }
     *   ]
     * }
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = auth()->user();

        return ChatResource::collection($user->chats()->get());
    }
}
