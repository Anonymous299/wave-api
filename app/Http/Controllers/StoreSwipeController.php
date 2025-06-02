<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'swipee_id' => 'required|uuid|exists:users,id',
            'direction' => 'required|in:left,right',
        ]);

        $swipe = auth()->user()
            ->swipes()
            ->create([
                'swipee_id' => $request->swipee_id,
                'direction' => $request->direction,
            ]);

        return response()->json([
            'swipe' => $swipe->toArray(),
            'match' => $swipe->isMatch(),
        ], Response::HTTP_CREATED);
    }
}
