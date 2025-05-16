<?php

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Clickbar\Magellan\Database\PostgisFunctions\ST;

$user = User::factory()->create([
    'name'     => 'Ryan Enns',
    'location' => Point::makeGeodetic(43.47734, -80.51496),
]);

User::factory()->create([
    'location' => Point::makeGeodetic(43.4735, -80.51456),
]);

$distanceInKm = 50;

$distances = User::query()
    ->select()
    ->addSelect(
        ST::distanceSphere($user->location, 'location')
            ->as('distance')
    )
    ->where(
        ST::distanceSphere($user->location, 'location'), '<=', $distanceInKm * 1000
    )
    ->orderBy('distance')
    ->get()
    ->map(fn($u) => [
        'id'       => $u->id,
        'distance' => $u->distance,
    ]);

dump($distances);
