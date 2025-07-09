<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UnblockUserController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|exists:users,id']);

        $userIdToUnblock = $request->input('id');

        /** @var User $user */
        $user = auth()->user();
        if (
            $user->getKey() === $userIdToUnblock ||
            !$user->blockedUsers()->where('blocked_id', $userIdToUnblock)->exists()
        ) {
            return response()->json(['error' => 'ID cannot be unblocked'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->unblock($userIdToUnblock);

        return response()->json([], Response::HTTP_OK);
    }
}
