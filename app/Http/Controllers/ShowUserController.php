<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class ShowUserController extends Controller
{
    public function __invoke(Request $request): UserResource|JsonResponse
    {
        $request->validate(['id' => 'required|string']);

        $user = User::query()->findOrFail($request->input('id'));

        if (
            $user->swipes()
                ->where(
                    'swipee_id',
                    auth()->id())->where('direction', 'right'
                )
                ->first()
                ?->isMatch()
        ) {
            return response()->json(['User ID ' . $request->input('id') . ' has swiped on you'], 422);
        }

        return new UserResource($user);
    }
}
