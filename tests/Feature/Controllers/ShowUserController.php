<?php

namespace Feature\Controllers;

use App\Models\Swipe;
use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowUserController extends TestCase
{
    use RefreshDatabase;

    public function test_it_errors_if_users_have_matched_already()
    {
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        Swipe::factory()->create([
            'swiper_id' => $userOne->id,
            'swipee_id' => $userTwo->id,
            'direction' => 'right',
        ]);

        Swipe::factory()->create([
            'swiper_id' => $userTwo->id,
            'swipee_id' => $userOne->id,
            'direction' => 'right',
        ]);

        $this->actingAs($userOne)->get('/api/users/user?id=' . $userTwo->id)
            ->assertStatus(422);
    }
}
