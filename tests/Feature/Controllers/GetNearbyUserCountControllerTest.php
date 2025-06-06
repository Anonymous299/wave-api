<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GetNearbyUserCountControllerTest extends TestCase
{
    use DatabaseTransactions;

    private const TEST_LAT_A = 43.4779751;
    private const TEST_LNG_A = -80.5197298;

    private const TEST_LAT_B = 43.470105;
    private const TEST_LNG_B = -80.512739;

    private const EXPECTED_DISTANCE = 1041.17488371; // Distance in meters

    public function test_it_returns_number_of_users_nearby()
    {
        User::factory()->create([
            'location' => Point::makeGeodetic(self::TEST_LAT_A, self::TEST_LNG_A),
        ]);

        User::factory()->create([
            'location' => Point::makeGeodetic(40.0, -85.0),
        ]);

        $userTwo = User::factory()->create([
            'location' => Point::makeGeodetic(self::TEST_LAT_B, self::TEST_LNG_B),
        ]);

        $this->actingAs($userTwo)->getJson(route('users.nearby.count', [
            'distance' => 50,
        ]))->assertOk()->assertJson([
            'count' => 1,
            'distance' => 50,
            'origin' => [
                'latitude' => self::TEST_LAT_B,
                'longitude' => self::TEST_LNG_B,
            ],
        ]);
    }
}
