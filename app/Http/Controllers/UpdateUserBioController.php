<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class UpdateUserBioController extends Controller
{
    /**
     * Update User Bio
     *
     * Updates the authenticated user's bio information.
     * Accepts a nested `bio` object with optional fields.
     *
     * @bodyParam bio object required The user's bio information.
     * @bodyParam bio.gender string Optional. The user's gender. Example: male
     * @bodyParam bio.age integer Optional. The user's age. Must be between 18 and 100. Example: 28
     * @bodyParam bio.job string Optional. The user's job title. Example: Software Engineer
     * @bodyParam bio.company string Optional. The user's company name. Example: Acme Corp
     * @bodyParam bio.education string Optional. The user's education information. Example: B.Sc. Computer Science
     * @bodyParam bio.about string Optional. A short description about the user. Example: Passionate about building scalable systems.
     *
     * @response 200 {}
     * @response 201 {}
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bio.gender'    => 'string',
            'bio.age'       => 'numeric|min:18|max:100',
            'bio.job'       => 'string',
            'bio.company'   => 'string',
            'bio.education' => 'string',
            'bio.about'     => 'string',
        ]);

        /** @var User $user */
        $user = auth()->user();
        $bio = $user->bio()->updateOrCreate([], Arr::get($validated, 'bio'));

        return $bio->wasRecentlyCreated ? response()->json([], 201) : response()->json([]);
    }
}
