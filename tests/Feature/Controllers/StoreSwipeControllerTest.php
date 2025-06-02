<?php

namespace Tests\Feature\Controllers;

use App\Models\Chat;
use App\Models\Swipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreSwipeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_swipes()
    {
        $swiper = User::factory()->create();

        $swipee = User::factory()->create();

        $response = $this->actingAs($swiper)->postJson('/api/swipes', [
            'swipee_id' => $swipee->getKey(),
            'direction' => 'left',
        ]);

        $response->assertCreated();
        $response->assertJson([
            'swipe' => Swipe::query()->first()->toArray(),
            'match' => false,
        ]);

        $this->assertDatabaseHas('swipes', [
            'swiper_id' => $swiper->getKey(),
            'swipee_id' => $swipee->getKey(),
            'direction' => 'left',
        ]);
    }

    /**
     * @return void
     * @dataProvider provideInvalidParameters
     */
    public function test_it_returns_unprocessable_for_invalid_parameters($parameters)
    {
        $swiper = User::factory()->create();
        $swipee = User::factory()->create();

        $response = $this->actingAs($swiper)->postJson('/api/swipes', $parameters);
        $response->assertUnprocessable();

        $this->assertDatabaseMissing('swipes', [
            'swiper_id' => $swiper->getKey(),
            'swipee_id' => $swipee->getKey(),
        ]);
    }

    public function test_it_returns_true_if_match_created()
    {
        $swiper = User::factory()->create();
        $swipee = User::factory()->create();

        Swipe::factory()->create([
            'swiper_id' => $swipee->getKey(),
            'swipee_id' => $swiper->getKey(),
            'direction' => 'right',
        ]);

        $this->actingAs($swiper)->postJson('/api/swipes', [
            'swipee_id' => $swipee->getKey(),
            'direction' => 'right',
        ])->assertCreated()->assertJson([
            'match'   => true,
            'chat_id' => Chat::query()->firstOrFail()->getKey(),
        ]);

        $this->assertDatabaseHas('chats', [
            'user_one_id' => $swiper->getKey(),
            'user_two_id' => $swipee->getKey(),
        ]);
    }

    public static function provideInvalidParameters(): array
    {
        return [
            'invalid swipee ID' => [['swipee_id' => 'invalid', 'direction' => 'left']],
            'invalid direction' => [['swipee_id' => Str::uuid(), 'direction' => 'invalid']],
            'no direction'      => [['swipee_id' => Str::uuid()]],
            'no swipee ID'      => [['direction' => 'left']],
            'no data'           => [[]],
        ];
    }
}
