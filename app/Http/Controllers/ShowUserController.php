<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class ShowUserController extends Controller
{
    public function __invoke(Request $request): UserResource
    {
        $request->validate(['id' => 'required|string']);

        $user = User::query()->findOrFail($request->input('id'));

        return new UserResource($user);
    }
}
