<?php

namespace App\Http\Controllers;

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Clickbar\Magellan\Database\PostgisFunctions\ST;
use Illuminate\Http\Request;

class GetNearbyUsers extends Controller
{
    /**
     * Get Nearby Users
     *
     * Retrieves users within a specified distance from the given latitude and longitude.
     * If latitude and longitude are not provided, the auth'd user's location will be used.
     *
     * these are query params
     *
     * @queryParam latitude float The latitude of the user's location.
     * @queryParam longitude float The longitude of the user's location.
     * @queryParam distance float required The distance in kilometers to search for users.
     *
     * @response 200 {
     *   "users": [
     *     {id: "98cca5ca-ca31-4031-a41b-241dc0876d5f" name: "John Doe" distance: 1234},
     *   ]
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'latitude'  => 'required_with:longitude|numeric',
            'longitude' => 'required_with:latitude|numeric',
            'distance'  => 'required|numeric|min:1|max:1000',
        ]);

        /** @var User $authUser */
        $authUser = auth()->user();

        $distanceInKm = $request->input('distance');
        $origin = $request->input('latitude') !== null ? Point::makeGeodetic(
            $request->input('latitude'),
            $request->input('longitude')
        ) : $authUser->location;

//        $userHasSwipedOn = $authUser->swipes()->get()->map(fn(Swipe $s) => $s->swipee_id);

        return User::query()
            ->select()
            ->addSelect(
                ST::distanceSphere(
                    $origin, 'location'
                )->as('distance')
            )
            ->where(
                ST::distanceSphere($origin, 'location'), '<=', $distanceInKm * 1000
            )
            ->where('id', '!=', $authUser->getKey())
//            ->whereNotIn('id', $userHasSwipedOn)
            ->orderBy('distance')
            ->get()
            ->map(fn($user) => [
                ...$user->toArray(),
                'distance' => $user->distance / 1000,
            ]);
    }
}
