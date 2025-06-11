<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdateUserController extends Controller
{
    /**
     * Update User
     *
     * Updates the authenticated user's bio information.
     * Accepts a nested `bio` object with optional fields.
     *
     * @bodyParam fcm_token string Optional. The Firebase Cloud Messaging token for the user's device. Example: fMEQMOF0xEELbP7icvPD:APA91bHQOcmVEbg
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
            'fcm_token'     => 'string',
            'intention'     => 'string|in:intimacy,business,friendship',
            'bio.gender'    => 'string',
            'bio.age'       => 'numeric|min:18|max:100',
            'bio.job'       => 'string',
            'bio.company'   => 'string',
            'bio.education' => 'string',
            'bio.about'     => 'string',
            'bio.images'    => 'array',
            'bio.images.*'  => 'string', // base64 strings
        ]);

        /** @var User $user */
        $user = auth()->user();
        $bioArray = Arr::get($validated, 'bio');

        if (isset($bioArray['images']) && is_array($bioArray['images'])) {
            $storedPaths = [];

            foreach ($bioArray['images'] as $base64Image) {
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                    $image = substr($base64Image, strpos($base64Image, ',') + 1);
                    $image = base64_decode($image);

                    $extension = strtolower($type[1]);
                    $filename = 'bio_images/' . Str::uuid() . '.' . $extension;

                    Storage::disk('public')->put($filename, $image);
                    $storedPaths[] = $filename;
                }
            }

            $bioArray['images'] = $storedPaths;
        }

        $bio = null;
        if ($bioArray) {
            $bio = $user->bio()->updateOrCreate([], $bioArray);
        }

        if ($request->input('fcm_token')) {
            $user->update(['fcm_token' => $request->input('fcm_token')]);
        }

        if ($request->input('intention')) {
            $user->update(['intention' => $request->input('intention')]);
        }

        return $bio && $bio->wasRecentlyCreated ? response()->json([], 201) : response()->json([]);
    }
}
