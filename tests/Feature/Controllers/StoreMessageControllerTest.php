<?php

namespace Tests\Feature\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreMessageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_message()
    {
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        $chat = Chat::query()->create([
            'user_one_id' => $userOne->getKey(),
            'user_two_id' => $userTwo->getKey(),
        ]);

        $response = $this->actingAs($userOne)->postJson('/api/messages', [
            'chat_id' => $chat->getKey(),
            'body' => 'Hello, world.',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('messages', [
            'chat_id' => $chat->getKey(),
            'sender_id' => $userOne->getKey(),
            'body' => 'Hello, world.',
        ]);
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
            'body' => 'Nope',
        ])->assertNotFound();

        $this->assertDatabaseMissing('messages', [
            'chat_id' => $unrelatedChat->getKey(),
            'body' => 'Nope',
        ]);
    }

    public static function provideInvalidParameters(): array
    {
        return [
            'missing chat_id' => [['body' => 'Hello']],
            'missing message' => [['chat_id' => (string) Str::uuid()]],
            'empty message'   => [['chat_id' => (string) Str::uuid(), 'body' => '']],
            'invalid chat_id' => [['chat_id' => 'not-a-uuid', 'body' => 'Test']],
        ];
    }
}
