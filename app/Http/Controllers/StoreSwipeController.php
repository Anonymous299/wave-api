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
     * Stores a swipe for the authenticated user.
     *
     * @bodyParam swipee_id string required The ID of the user being swiped on.
     * @bodyParam direction string required The direction of the swipe (left or right).
     *
     * @response 201 {
     * "id": 1,
     * "swipee_id": "08e1608c-eb31-4623-bde6-b63646daecf9",
     * "swiper_id": "98cca5ca-ca31-4031-a41b-241dc0876d5f",
     * "direction": "right"
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

        return response()->json($swipe, Response::HTTP_CREATED);
    }
}
