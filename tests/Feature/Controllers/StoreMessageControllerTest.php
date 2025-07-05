<?php

namespace Tests\Feature\Controllers;

use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\User;
use App\Notifications\TextReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreMessageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_message_and_broadcasts_event()
    {
        Event::fake();

        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        $chat = Chat::query()->create([
            'user_one_id' => $userOne->getKey(),
            'user_two_id' => $userTwo->getKey(),
        ]);

        $response = $this->actingAs($userOne)->postJson('/api/messages', [
            'chat_id' => $chat->getKey(),
            'body'    => 'Hello, world.',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('messages', [
            'chat_id'   => $chat->getKey(),
            'sender_id' => $userOne->getKey(),
            'body'      => 'Hello, world.',
        ]);

        Event::assertDispatched(
            MessageSent::class,
            fn($event) => $event->message->chat_id === $chat->getKey()
                && $event->message->sender_id === $userOne->getKey()
                && $event->message->body === 'Hello, world.'
        );
    }

    /**
     * @dataProvider provideInvalidParameters
     */
    public function test_it_returns_unprocessable_for_invalid_parameters(array $parameters)
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->postJson('/api/messages', $parameters)
            ->assertUnprocessable();
    }

    public function test_it_forbids_sending_to_unrelated_chat()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $tertiary = User::factory()->create();
        $unrelatedChat = Chat::query()->create([
            'user_one_id' => $other->getKey(),
            'user_two_id' => $tertiary->getKey(),
        ]);

        $this->actingAs($user)->postJson('/api/messages', [
            'chat_id' => $unrelatedChat->getKey(),
            'body'    => 'Nope',
        ])->assertNotFound();

        $this->assertDatabaseMissing('messages', [
            'chat_id' => $unrelatedChat->getKey(),
            'body'    => 'Nope',
        ]);
    }

    public function test_messages_end_up_in_correct_chats()
    {
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();
        $userThree = User::factory()->create();

        $chat = Chat::query()->create([
            'user_one_id' => $userOne->getKey(),
            'user_two_id' => $userTwo->getKey(),
        ]);

        $chatTwo = Chat::query()->create([
            'user_one_id' => $userOne->getKey(),
            'user_two_id' => $userThree->getKey(),
        ]);

        $this->actingAs($userOne)->postJson('/api/messages', [
            'chat_id' => $chat->getKey(),
            'body'    => 'Hello, world.',
        ])->assertCreated();

        $this->actingAs($userOne)->postJson('/api/messages', [
            'chat_id' => $chatTwo->getKey(),
            'body'    => 'Hi there!',
        ])->assertCreated();

        $this->assertDatabaseCount('messages', 2);

        $this->assertDatabaseHas('messages', [
            'chat_id'   => $chat->getKey(),
            'sender_id' => $userOne->getKey(),
            'body'      => 'Hello, world.',
        ]);

        $this->assertDatabaseHas('messages', [
            'chat_id'   => $chatTwo->getKey(),
            'sender_id' => $userOne->getKey(),
            'body'      => 'Hi there!',
        ]);
    }

    public function test_it_rejects_if_user_not_in_chat()
    {
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        $chat = Chat::query()->create([
            'user_one_id' => $userOne->getKey(),
            'user_two_id' => $userTwo->getKey(),
        ]);

        $unrelatedUser = User::factory()->create();

        $this->actingAs($unrelatedUser)->postJson('/api/messages', [
            'chat_id' => $chat->getKey(),
            'body'    => 'This should not work',
        ])->assertNotFound();
    }

    public function test_it_notifies_users_of_message()
    {
        Notification::fake();

        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        $chat = Chat::query()->create([
            'user_one_id' => $userOne->getKey(),
            'user_two_id' => $userTwo->getKey(),
        ]);

        $response = $this->actingAs($userOne)->postJson('/api/messages', [
            'chat_id' => $chat->getKey(),
            'body'    => 'Hello, world.',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('messages', [
            'chat_id'   => $chat->getKey(),
            'sender_id' => $userOne->getKey(),
            'body'      => 'Hello, world.',
        ]);

        Notification::assertSentTo($userTwo, TextReceived::class);
    }

    public function test_it_disallows_messaging_blocked_users()
    {
        Event::fake();

        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        $userOne->block($userTwo);

        $chat = Chat::query()->create([
            'user_one_id' => $userOne->getKey(),
            'user_two_id' => $userTwo->getKey(),
        ]);

        $response = $this->actingAs($userOne)->postJson('/api/messages', [
            'chat_id' => $chat->getKey(),
            'body'    => 'Hello, world.',
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseMissing('messages', [
            'chat_id'   => $chat->getKey(),
            'sender_id' => $userOne->getKey(),
            'body'      => 'Hello, world.',
        ]);

        Event::assertNotDispatched(MessageSent::class);
    }

    public static function provideInvalidParameters(): array
    {
        return [
            'missing chat_id' => [['body' => 'Hello']],
            'missing message' => [['chat_id' => (string)Str::uuid()]],
            'empty message'   => [['chat_id' => (string)Str::uuid(), 'body' => '']],
            'invalid chat_id' => [['chat_id' => 'not-a-uuid', 'body' => 'Test']],
        ];
    }
}
