<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_bio_if_non_existent()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/users/me', [
            'bio' => [
                'gender'    => 'male',
                'age'       => 19,
                'job'       => 'Snickers Bar QA',
                'company'   => 'KitKat Inc.',
                'education' => 'MSc in Snickers Science',
                'about'     => 'this is a test bio',
            ]
        ]);

        $response->assertSuccessful();
        $response->assertCreated();
    }

    public function test_it_updates_bio_if_exists()
    {
        $user = User::factory()->create();
        $bio = $user->bio()->create([
            'gender'    => 'male',
            'age'       => 19,
            'job'       => 'Snickers Bar QA',
            'company'   => 'KitKat Inc.',
            'education' => 'MSc in Snickers Science',
            'about'     => 'this is a test bio',
        ]);
        $this->assertDatabaseCount('bios', 1);

        $expected = [
            'gender'    => 'female',
            'age'       => 21,
            'job'       => 'KitKat Bar QA',
            'company'   => 'Snickers Inc.',
            'education' => 'MSc in KitKat Science',
            'about'     => 'this is NOT a test bio',
        ];
        $response = $this->actingAs($user)->postJson('/api/users/me', ['bio' => $expected]);

        $response->assertStatus(200);

        $this->assertDatabaseCount('bios', 1);
        $this->assertDatabaseHas('bios', [...$expected, 'id' => $bio->getKey()]);
    }

    public function test_authenticated_user_can_update_fcm_token()
    {
        $user = User::factory()->create();
        $token = 'fake_token_123';

        $response = $this->actingAs($user)->postJson('/api/users/me', [
            'fcm_token' => $token,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'fcm_token' => $token,
        ]);
    }

    public function test_unauthenticated_user_cannot_update_fcm_token()
    {
        $response = $this->postJson('/api/users/me', [
            'fcm_token' => 'some_token',
        ]);

        $response->assertUnauthorized();
    }
}
