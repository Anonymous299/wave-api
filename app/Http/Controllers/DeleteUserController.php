<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeleteUserController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $user = auth()->user();

        collect($user->bio->images)->each(function ($image) {
            $uri = explode('.com/', $image)[1];

            if (!$uri) {
                Log::error('Malformed image URL - ' . $image);
            }

            Storage::delete($uri);
        });

        $user->delete();

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
