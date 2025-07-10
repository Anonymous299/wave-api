<?php

namespace Tests\Feature\Controllers;

use App\Models\Chat;
use App\Models\Swipe;
use App\Models\User;
use App\Notifications\MatchCreated;
use App\Notifications\TextReceived;
use App\Notifications\UserWaved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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

    public function test_it_sends_match_notifications()
    {
        Notification::fake();

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
        ])->assertCreated();

        Notification::assertSentTo($swiper, MatchCreated::class);
        Notification::assertSentTo($swipee, MatchCreated::class);
    }

    public function test_it_prevents_swiping_on_self()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/swipes', [
            'swipee_id' => $user->getKey(),
            'direction' => 'right',
        ]);

        $response->assertUnprocessable();
    }

    public function test_it_notifies_user_if_they_are_swiped_on()
    {
        Notification::fake();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($user2)->postJson('/api/swipes', [
            'swipee_id' => $user1->getKey(),
            'direction' => 'right',
        ]);

        $response->assertCreated();

        Notification::assertSentTo($user1, UserWaved::class);
    }

    public function test_it_sends_nearby_message_when_swiping_right_on_existing_match()
    {
        Notification::fake();

        $swiper = User::factory()->create();
        $swipee = User::factory()->create();

        // Create existing match
        $swiper->swipes()->create([
            'swipee_id' => $swipee->getKey(),
            'direction' => 'right',
        ]);

        $swipee->swipes()->create([
            'swipee_id' => $swiper->getKey(),
            'direction' => 'right',
        ]);

        // Create existing chat
        $chat = Chat::create([
            'user_one_id' => $swiper->getKey(),
            'user_two_id' => $swipee->getKey(),
        ]);

        // Swipe right on existing match
        $response = $this->actingAs($swiper)->postJson('/api/swipes', [
            'swipee_id' => $swipee->getKey(),
            'direction' => 'right',
        ]);

        $response->assertCreated();
        $response->assertJson([
            'match' => true,
            'chat_id' => $chat->getKey(),
        ]);

        // Assert message was created
        $this->assertDatabaseHas('messages', [
            'chat_id' => $chat->getKey(),
            'sender_id' => $swiper->getKey(),
            'body' => '👋 We\'re nearby!',
        ]);

        // Assert notification was sent
        Notification::assertSentTo($swipee, TextReceived::class);
    }

    public function test_it_does_nothing_when_swiping_left_on_existing_match()
    {
        Notification::fake();

        $swiper = User::factory()->create();
        $swipee = User::factory()->create();

        // Create existing match
        $swiper->swipes()->create([
            'swipee_id' => $swipee->getKey(),
            'direction' => 'right',
        ]);

        $swipee->swipes()->create([
            'swipee_id' => $swiper->getKey(),
            'direction' => 'right',
        ]);

        // Create existing chat
        $chat = Chat::create([
            'user_one_id' => $swiper->getKey(),
            'user_two_id' => $swipee->getKey(),
        ]);

        // Swipe left on existing match
        $response = $this->actingAs($swiper)->postJson('/api/swipes', [
            'swipee_id' => $swipee->getKey(),
            'direction' => 'left',
        ]);

        $response->assertCreated();
        $response->assertJson([
            'match' => true,
            'chat_id' => $chat->getKey(),
        ]);

        // Assert no message was created
        $this->assertDatabaseMissing('messages', [
            'chat_id' => $chat->getKey(),
            'sender_id' => $swiper->getKey(),
            'body' => '👋 We\'re nearby!',
        ]);

        // Assert no notification was sent
        Notification::assertNotSentTo($swipee, TextReceived::class);
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
