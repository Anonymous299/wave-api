<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BlockUserController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|exists:users,id']);

        $userIdToBlock = $request->input('id');

        /** @var User $user */
        $user = auth()->user();
        if (
            $user->getKey() === $userIdToBlock ||
            $user->blockedUsers()->where('blocked_id', $userIdToBlock)->exists()
        ) {
            return response()->json(['error' => 'ID cannot be blocked'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->block($userIdToBlock);

        return response()->json([], Response::HTTP_OK);
    }
}
