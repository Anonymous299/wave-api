<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnblockUserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_unblocks_user()
    {
        $authUser = User::factory()->create();
        $blockedUser = User::factory()->create();

        // First, block the user
        $authUser->block($blockedUser->getKey());

        // Now unblock
        $response = $this->actingAs($authUser)->deleteJson(
            route('users.unblock'), ['id' => $blockedUser->getKey()]
        );

        $response->assertOk();

        $this->assertDatabaseMissing(
            'blocked_users',
            ['user_id' => $authUser->getKey(), 'blocked_id' => $blockedUser->getKey()]
        );
    }

    public function test_it_does_not_allow_unblocking_non_blocked_user()
    {
        $authUser = User::factory()->create();
        $userNotBlocked = User::factory()->create();

        $response = $this->actingAs($authUser)->deleteJson(
            route('users.unblock'), ['id' => $userNotBlocked->getKey()]
        );

        $response->assertUnprocessable();
    }

    public function test_it_does_not_allow_unblocking_yourself()
    {
        $authUser = User::factory()->create();

        $response = $this->actingAs($authUser)->deleteJson(
            route('users.unblock'), ['id' => $authUser->getKey()]
        );

        $response->assertUnprocessable();
    }
}
