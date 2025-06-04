<?php

namespace App\Http\Controllers;

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeUserLocationController extends Controller
{

    /**
     * Change User Location
     *
     * Updates the location of the authenticated user.
     *
     * @bodyParam latitude float required The latitude of the user.
     * @bodyParam longitude float required The longitude of the user.
     *
     * @response 200 {
     *   "message": "Location updated successfully."
     *   "user_id": "08e1608c-eb31-4623-bde6-b63646daecf9",
     * }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        /** @var User|null $user */
        $user = Auth::user();

        $user->update([
            'location' => Point::makeGeodetic(
                $request->input('latitude'),
                $request->input('longitude')
            ),
        ]);

        return response()->json([
            'message' => 'Location updated successfully.',
            'user_id' => $user->getKey(),
        ]);
    }
}
