<?php

namespace Tests\Integration;

use App\Models\User;
use Tests\TestCase;

class AuthToSwipeTest extends TestCase
{
    public function test_it_can_auth_and_swipe(): void
    {
        $swiper = User::factory()->create();

        $response = $this->postJson(route('auth.login', [
            'email'       => $swiper->email,
            'password'    => 'password',
            'device_name' => 'test-device',
        ]))->assertOk();

        $token = $response->json()['token'];

        $swipee = User::factory()->create();


        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson(route('swipes.store'), [
            'swipee_id' => $swipee->getKey(),
            'direction' => 'left',
        ])->assertCreated();

        $this->assertDatabaseHas('swipes', [
            'swiper_id' => $swiper->getKey(),
            'swipee_id' => $swipee->getKey(),
            'direction' => 'left',
        ]);
    }
}
