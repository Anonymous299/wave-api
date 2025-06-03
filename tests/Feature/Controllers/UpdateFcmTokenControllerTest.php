<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateFcmTokenControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_fcm_token()
    {
        $user = User::factory()->create();
        $token = 'fake_token_123';

        $response = $this->actingAs($user)->postJson('/api/users/me', [
            'fcm_token' => $token,
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'FCM token updated successfully']);

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

    public function test_fcm_token_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/users/me', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('fcm_token');
    }
}
