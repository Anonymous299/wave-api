<?php

namespace Tests\Feature\Models;

use App\Models\Swipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SwipeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_chat_on_match()
    {
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        Swipe::factory()->create([
            'swiper_id' => $userOne->getKey(),
            'swipee_id' => $userTwo->getKey(),
            'direction' => 'right',
        ]);

        Swipe::factory()->create([
            'swiper_id' => $userTwo->getKey(),
            'swipee_id' => $userOne->getKey(),
            'direction' => 'right',
        ]);

        $this->assertDatabaseHas('chats', [
            'user_one_id' => $userTwo->getKey(),
            'user_two_id' => $userOne->getKey(),
        ]);
    }

    public function test_it_does_not_create_chat_on_non_match()
    {
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        Swipe::factory()->create([
            'swiper_id' => $userOne->getKey(),
            'swipee_id' => $userTwo->getKey(),
            'direction' => 'right',
        ]);

        Swipe::factory()->create([
            'swiper_id' => $userTwo->getKey(),
            'swipee_id' => $userOne->getKey(),
            'direction' => 'left',
        ]);

        $this->assertDatabaseMissing('chats', [
            'user_one_id' => $userTwo->getKey(),
            'user_two_id' => $userOne->getKey(),
        ]);
    }
}
