<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Notifications\MatchCreated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class StoreSwipeController extends Controller
{
    /**
     * Store Swipe
     *
     * Stores a swipe for the authenticated user. Returns the swipe as an object and a match key to indicate if
     * the swipe resulted in a match.
     *
     * @bodyParam swipee_id string required The ID of the user being swiped on. Example: 08e1608c-eb31-4623-bde6-b63646daecf9
     * @bodyParam direction string required The direction of the swipe (left or right). Example: right
     *
     * @responseStatus 201
     * @response {
     *     "swipe": {
     *         "id": "12345",
     *         "swipee_id": "08e1608c-eb31-4623-bde6-b63646daecf9",
     *         "direction": "right",
     *         "created_at": "2023-10-01T12:00:00Z",
     *     },
     *    "match": true
     *    "chat_id": "58c8f668-e178-481c-94c4-28eb1e9b133b"
     * }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'swipee_id' => 'required|uuid|exists:users,id',
            'direction' => 'required|in:left,right',
        ]);

        if ((string)$request->swipee_id === (string)$request->user()->getKey()) {
            return response()->json([
                'message' => 'You cannot swipe on yourself.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $swipe = auth()->user()
            ->swipes()
            ->create([
                'swipee_id' => $request->swipee_id,
                'direction' => $request->direction,
            ]);

        $chat = null;
        // if ($swipe->isMatch()) {
        //     $chat = Chat::query()->create([
        //         'user_one_id' => $swipe->swiper->getKey(),
        //         'user_two_id' => $swipe->swipee->getKey(),
        //     ]);

        //     $swipe->swiper->notify(new MatchCreated($swipe->swipee, $chat));
        //     $swipe->swipee->notify(new MatchCreated($swipe->swiper, $chat));
        // }

        // else{
            $swipe->swipee->notify(new UserWaved($swipe->swipee));
        // }

        return response()->json([
            'swipe'   => $swipe->toArray(),
            'match'   => $swipe->isMatch(),
            'chat_id' => $chat?->getKey(),
        ], Response::HTTP_CREATED);
    }
}
