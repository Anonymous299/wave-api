<?php

namespace App\Http\Controllers;

use App\Models\User;
use Clickbar\Magellan\Database\PostgisFunctions\ST;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetNearbyUserCountController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     *
     * Get Nearby User Count
     *
     * Retrieves the count of users within a specified distance from the authenticated user's location.
     *
     * @queryParam distance float required The distance in kilometers to search for users.
     *
     * @response 200 {
     *  "count": 5,
     *  "distance": 50,
     *  "origin": {
     *    "latitude": 43.4779751,
     *    "longitude": -80.5197298
     *  }
     * }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate(['distance' => 'required|numeric|min:1|max:1000']);

        /** @var User $authUser */
        $authUser = auth()->user();

        $distanceInKm = $request->input('distance');
        $origin = $authUser->location;

        $count = User::query()
            ->selectRaw('COUNT(*) as count')
            ->addSelect(
                ST::distanceSphere(
                    $origin, 'location'
                )->as('distance')
            )
            ->where(
                ST::distanceSphere($origin, 'location'), '<=', $distanceInKm * 1000
            )
            ->where('id', '!=', $authUser->getKey())
            ->whereNotNull('name')
            ->count();

        return response()->json([
            'count' => $count,
            'distance' => $distanceInKm,
            'origin' => [
                'latitude' => $authUser->location->getLatitude(),
                'longitude' => $authUser->location->getLongitude(),
            ],
        ]);
    }
}
