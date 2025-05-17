<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetNearbyUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_gets_nearby_users()
    {
        $expectedUser = User::factory()->create([
            'location' => Point::makeGeodetic(43.4735, -80.51456),
        ]);

        $otherUser = User::factory()->create([
            'location' => Point::makeGeodetic(44.47734, -81.51496),
        ]);

        $this->actingAs($otherUser)->get(route('users.nearby', [
            'latitude'  => 43.47734,
            'longitude' => -80.51496,
            'distance'  => 50,
        ]))->assertOk()->assertJson([
            [
                'id'       => $expectedUser->getKey(),
                'distance' => 428.20726285,
            ],
        ]);
    }
}
