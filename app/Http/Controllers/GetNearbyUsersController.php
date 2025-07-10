<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Chat;
use App\Models\Swipe;
use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Clickbar\Magellan\Database\PostgisFunctions\ST;
use Illuminate\Http\Request;

class GetNearbyUsersController extends Controller
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
     * "data": [
     * {
     * "id": "a730521f-88ae-423d-96c8-dc3e537c3d5d",
     * "name": "Jan Nicolas",
     * "email": "godfrey85@example.net",
     * "email_verified_at": "2025-06-06T18:46:05.000000Z",
     * "location": {
     * "type": "Point",
     * "coordinates": [
     * -80.5197298,
     * 43.4779751
     * ]
     * },
     * "created_at": "2025-06-06T18:46:05.000000Z",
     * "updated_at": "2025-06-06T18:46:05.000000Z",
     * "fcm_token": null,
     * "distance": 1041.17488371,
     * "bio": {
     * "id": "01974690-8202-72e6-a880-cab892a0d332",
     * "gender": "male",
     * "age": 76,
     * "job": "Irradiated-Fuel Handler",
     * "company": "Bashirian, O'Connell and Okuneva",
     * "education": "BSc",
     * "about": "Perspiciatis sed modi quidem debitis. Accusantium dicta quam ex voluptatum. Excepturi tempora corrupti accusantium.",
     * "user_id": "a730521f-88ae-423d-96c8-dc3e537c3d5d"
     * }
     * }
     * ]
     * }
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

        $userHasSwipedOn = $authUser->swipes()
            ->select('swipee_id')
            ->get()
            ->map(fn(Swipe $s) => $s->swipee_id);

        return UserResource::collection(User::query()
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
            ->where('intention', $authUser->intention)
            ->whereNotNull('name')
            ->orderBy('distance')
            ->get());
    }
}
