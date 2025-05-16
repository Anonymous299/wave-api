<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreSwipeController extends Controller
{
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
