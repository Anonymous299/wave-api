<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockUserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_blocked_user()
    {
        $authUser = User::factory()->create();
        $userToBlock = User::factory()->create();

        $response = $this->actingAs($authUser)->postJson(
            route('users.block'), ['id' => $userToBlock->getKey()]
        );

        $response->assertOk();

        $this->assertDatabaseHas(
            'blocked_users',
            ['user_id' => $authUser->getKey(), 'blocked_id' => $userToBlock->getKey()]
        );
    }

    public function test_it_does_not_allow_blocking_twice()
    {
        $authUser = User::factory()->create();
        $userToBlock = User::factory()->create();

        $this->actingAs($authUser)->postJson(route('users.block'), ['id' => $userToBlock->getKey()]);

        $failureResponse = $this->actingAs($authUser)->postJson(
            route('users.block'), ['id' => $userToBlock->getKey()]
        );

        $failureResponse->assertUnprocessable();
    }

    public function test_it_does_not_allow_blocking_yourself()
    {
        $authUser = User::factory()->create();

        $response = $this->actingAs($authUser)->postJson(
            route('users.block'), ['id' => $authUser->getKey()]
        );

        $response->assertUnprocessable();
    }
}
