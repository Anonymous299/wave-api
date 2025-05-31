<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetNearbyUsersTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_LAT_A = 43.4779751;
    private const TEST_LNG_A = -80.5197298;

    private const TEST_LAT_B = 43.470105;
    private const TEST_LNG_B = -80.512739;

    private const EXPECTED_DISTANCE = 1041.17488371 / 1000; // Distance in meters

    public function test_it_gets_nearby_users()
    {
        $expectedUser = User::factory()->create([
            'location' => Point::makeGeodetic(self::TEST_LAT_A, self::TEST_LNG_A),
        ]);

        $otherUser = User::factory()->create([
            'location' => Point::makeGeodetic(self::TEST_LAT_B, self::TEST_LNG_B),
        ]);

        $this->actingAs($otherUser)->get(route('users.nearby', [
            'latitude'  => self::TEST_LAT_B,
            'longitude' => self::TEST_LNG_B,
            'distance'  => 50,
        ]))->assertOk()->assertJson([
            [
                'id'       => $expectedUser->getKey(),
                'distance' => self::EXPECTED_DISTANCE
            ],
        ]);
    }

    public function test_it_requires_both_dimensions_if_provided()
    {
        $this->actingAs(User::factory()->create())->getJson(route('users.nearby', [
            'latitude' => 43.47734,
            'distance' => 50,
        ]))->assertStatus(422)->assertJsonValidationErrors(['longitude']);

        $this->actingAs(User::factory()->create())->getJson(route('users.nearby', [
            'longitude' => -80.51496,
            'distance'  => 50,
        ]))->assertStatus(422)->assertJsonValidationErrors(['latitude']);
    }

    public function test_it_uses_auth_user_if_no_coordinates_provided()
    {
        $expectedUser = User::factory()->create([
            'location' => Point::makeGeodetic(self::TEST_LAT_A, self::TEST_LNG_A),
        ]);

        $otherUser = User::factory()->create([
            'location' => Point::makeGeodetic(self::TEST_LAT_B, self::TEST_LNG_B),
        ]);

        $this->actingAs($expectedUser)->get(route('users.nearby', [
            'distance' => 1000,
        ]))->assertOk()->assertJson([
            [
                'id'       => $otherUser->getKey(),
                'distance' => self::EXPECTED_DISTANCE
            ],
        ]);
    }
}
