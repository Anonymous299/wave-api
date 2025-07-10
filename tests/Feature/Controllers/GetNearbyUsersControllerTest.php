<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GetNearbyUsersControllerTest extends TestCase
{
    use DatabaseTransactions;

    private const TEST_LAT_A = 43.4779751;
    private const TEST_LNG_A = -80.5197298;

    private const TEST_LAT_B = 43.470105;
    private const TEST_LNG_B = -80.512739;

    private const EXPECTED_DISTANCE = 1041.17488371 / 1000; // Distance in meters

    public function test_it_gets_nearby_users()
    {
        $expectedUser = User::factory()
            ->withBio()
            ->create([
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
            'data' => [
                [
                    'id'       => $expectedUser->getKey(),
                    'distance' => self::EXPECTED_DISTANCE,
                    'bio'      => ['id' => $expectedUser->bio()->first()->getKey()],
                    'has_matched' => false,
                ]
            ]
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
            'data' => [
                [
                    'id'       => $otherUser->getKey(),
                    'distance' => self::EXPECTED_DISTANCE,
                    'has_matched' => false
                ]
            ],
        ]);
    }

    public function test_it_does_not_return_people_you_have_already_swiped_on()
    {
        $this->markTestSkipped("Changing requirements");
        $this->withoutExceptionHandling();

        /** @var User $user */
        $user = User::factory()->create(
            ['location' => Point::makeGeodetic(self::TEST_LAT_A, self::TEST_LNG_A)]
        );
        $userTwo = User::factory()->create(
            ['location' => Point::makeGeodetic(self::TEST_LAT_A, self::TEST_LNG_A)]
        );

        $user->swipes()->create([
            'swipee_id' => $userTwo->getKey(),
            'direction' => 'right',
        ]);

        $response = $this->actingAs($user)->getJson(route('users.nearby', ['distance' => 1000]));
        $response->assertOk();
        $response->assertJsonMissing(['id' => $userTwo->getKey()]);
    }

    public function test_it_only_returns_users_with_matching_intention()
    {
        $authUser = User::factory()->create([
            'intention' => 'intimacy',
            'location'  => Point::makeGeodetic(self::TEST_LAT_A, self::TEST_LNG_A),
        ]);

        $matchingUser = User::factory()->create([
            'intention' => 'intimacy',
            'location'  => Point::makeGeodetic(self::TEST_LAT_B, self::TEST_LNG_B),
        ]);

        $nonMatchingUser = User::factory()->create([
            'intention' => 'business',
            'location'  => Point::makeGeodetic(self::TEST_LAT_B, self::TEST_LNG_B),
        ]);

        $response = $this->actingAs($authUser)->getJson(route('users.nearby', [
            'latitude'  => self::TEST_LAT_A,
            'longitude' => self::TEST_LNG_A,
            'distance'  => 5,
        ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $matchingUser->getKey(), 'has_matched' => false]);
        $response->assertJsonMissing(['id' => $nonMatchingUser->getKey()]);
    }

    public function test_it_only_returns_users_non_null_names()
    {
        $authUser = User::factory()->create([
            'location' => Point::makeGeodetic(self::TEST_LAT_A, self::TEST_LNG_A),
        ]);

        $notMatchingUser = User::factory()->create([
            'name'     => null,
            'location' => Point::makeGeodetic(self::TEST_LAT_B, self::TEST_LNG_B),
        ]);

        $matchingUser = User::factory()->create([
            'location' => Point::makeGeodetic(self::TEST_LAT_B, self::TEST_LNG_B),
        ]);

        $response = $this->actingAs($authUser)->getJson(route('users.nearby', [
            'latitude'  => self::TEST_LAT_A,
            'longitude' => self::TEST_LNG_A,
            'distance'  => 5,
        ]));

        $response->assertOk();
        $response->assertJsonFragment(['id' => $matchingUser->getKey(), 'has_matched' => false]);
        $response->assertJsonMissing(['id' => $notMatchingUser->getKey()]);
    }

    public function test_it_does_not_return_users_that_you_have_matched_with()
    {
        $authUser = User::factory()->create([
            'location' => Point::makeGeodetic(self::TEST_LAT_A, self::TEST_LNG_A),
        ]);
        $userThatHasMatched = User::factory()->create([
            'location' => Point::makeGeodetic(self::TEST_LAT_A, self::TEST_LNG_A),
        ]);
        $userThatHasNotMatched = User::factory()->create([
            'location' => Point::makeGeodetic(self::TEST_LAT_A, self::TEST_LNG_A),
        ]);

        $authUser->swipes()->create([
            'swipee_id' => $userThatHasMatched->getKey(),
            'direction' => 'right',
        ]);

        $userThatHasMatched->swipes()->create([
            'swipee_id' => $authUser->getKey(),
            'direction' => 'right',
        ]);

        $this->assertTrue($authUser->swipes()->first()->isMatch());
        $this->assertTrue($userThatHasMatched->swipes()->first()->isMatch());

        $response = $this->actingAs($authUser)->getJson(route('users.nearby', [
            'latitude'  => self::TEST_LAT_A,
            'longitude' => self::TEST_LNG_A,
            'distance'  => 5,
        ]));

        $response->assertOk();
        $response->assertJsonMissing(['id' => $userThatHasMatched->getKey()]);
        $response->assertJsonFragment(['id' => $userThatHasNotMatched->getKey(), 'has_matched' => false]);
    }
}
