<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class ShowUserController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
           'id' => 'required|string',
        ]);

        $user = User::findOrFail($request->input('id'));

        return response()->json(new UserResource($user));
    }
}
