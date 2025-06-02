<?php

namespace Tests\Feature\Controllers;

use App\Models\Chat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChatsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_chat_for_valid_id()
    {
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        $chat = Chat::query()->create([
            'user_one_id' => $userOne->getKey(),
            'user_two_id' => $userTwo->getKey(),
        ]);

        $chatMessages = collect([1, 2, 3, 4, 5])
            ->map(fn() => $chat->messages()->create([
                'sender_id' => $userOne->getKey(),
                'body'      => 'snickers',
            ]));



        $response = $this->actingAs($userOne)->getJson('/api/chats?chat_id=' . $chat->getKey());

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $chat->getKey(),
                'user_one' => [
                    'id'   => $userOne->getKey(),
                    'name' => $userOne->name,
                ],
                'user_two' => [
                    'id'   => $userTwo->getKey(),
                    'name' => $userTwo->name,
                ],
            ]);

        $response = json_decode($response->content());
        $this->assertEquals(Carbon::parse($response->created_at), $chat->created_at);
        $this->assertCount(5, $response->messages);
    }

    public function test_it_returns_unprocessable_for_invalid_uuid()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/chats?chat_id=not-a-uuid')
            ->assertUnprocessable();
    }

    public function test_it_returns_unprocessable_for_missing_chat_id()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/chats')
            ->assertUnprocessable();
    }

    public function test_it_returns_not_found_for_nonexistent_chat()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/chats?chat_id=' . Str::uuid())
            ->assertNotFound();
    }
}
