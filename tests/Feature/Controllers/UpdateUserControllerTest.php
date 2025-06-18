<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateUserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_bio_if_non_existent()
    {
        $this->assertDatabaseCount('bios', 0);
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
        $this->assertDatabaseCount('bios', 1);
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
            'id'        => $user->id,
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

    public function test_it_stores_uploaded_images_as_paths_in_bio()
    {
        $user = User::factory()->create();

        $base64Image = 'data:image/png;base64,' . base64_encode(file_get_contents(__DIR__ . '/Fixtures/test.png'));

        $response = $this->actingAs($user)->postJson('/api/users/me', [
            'bio' => [
                'images' => [$base64Image],
            ],
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseCount('bios', 1);
        $this->assertNotEmpty($user->fresh()->bio->images);
        $this->assertStringStartsWith('bio_images/', $user->bio->images[0]);
        $this->assertTrue(Storage::disk('public')->exists($user->bio->images[0]));
    }

    public function test_it_updates_intention()
    {
        $user = User::factory()->create(['intention' => 'friendship']);

        $response = $this->actingAs($user)->postJson('/api/users/me', [
            'intention' => 'intimacy',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'intention' => 'intimacy',
        ]);
    }

    public function test_it_updates_name()
    {
        $user = User::factory()->create(['name' => null]);

        $response = $this->actingAs($user)->postJson('/api/users/me', [
            'name' => 'John Doe',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'John Doe',
        ]);
    }
}
