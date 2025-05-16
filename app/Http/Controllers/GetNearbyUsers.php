<?php

namespace App\Http\Controllers;

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Clickbar\Magellan\Database\PostgisFunctions\ST;
use Illuminate\Http\Request;

class GetNearbyUsers extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'distance'  => 'required|numeric|min:1|max:1000',
        ]);

        $distanceInKm = $request->input('distance');
        $origin = Point::makeGeodetic(
            $request->input('latitude'),
            $request->input('longitude')
        );

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
            ->orderBy('distance')
            ->get()
            ->map(fn($user) => [
                ...$user->toArray(),
                'distance' => $user->distance,
            ]);
    }
}
