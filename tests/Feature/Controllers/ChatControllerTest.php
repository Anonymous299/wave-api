<?php

namespace Tests\Feature\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChatControllerTest extends TestCase
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

        collect(range(1, 5))->each(fn() => $chat->messages()->create([
            'sender_id' => $userOne->getKey(),
            'body'      => 'snickers',
        ]));

        $response = $this->actingAs($userOne)->getJson("/api/chats/{$chat->getKey()}");

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id'       => $chat->getKey(),
                'user_one' => [
                    'id'   => $userOne->getKey(),
                    'name' => $userOne->name,
                ],
                'user_two' => [
                    'id'   => $userTwo->getKey(),
                    'name' => $userTwo->name,
                ],
            ]);

        $data = json_decode($response->content());
        $this->assertEquals($chat->created_at->toISOString(), Carbon::parse($data->created_at)->toISOString());
        $this->assertCount(5, $data->messages);
    }

    public function test_it_returns_not_found_for_invalid_uuid()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/chats/not-a-uuid')
            ->assertNotFound();
    }

    public function test_it_returns_not_found_for_nonexistent_chat()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/chats/' . Str::uuid())
            ->assertNotFound();
    }

    public function test_it_returns_all_chats_for_authenticated_user()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $third = User::factory()->create();

        $chat1 = Chat::query()->create([
            'user_one_id' => $user->getKey(),
            'user_two_id' => $other->getKey(),
        ]);

        $chat2 = Chat::query()->create([
            'user_one_id' => $third->getKey(),
            'user_two_id' => $user->getKey(),
        ]);

        Chat::query()->create([
            'user_one_id' => $other->getKey(),
            'user_two_id' => $third->getKey(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/chats');

        $response->assertOk();
        $data = json_decode($response->content());
        $this->assertCount(2, $data->data);
        $response->assertJsonFragment(['id' => $chat1->id]);
        $response->assertJsonFragment(['id' => $chat2->id]);
    }

    public function test_it_requires_authentication()
    {
        $this->getJson('/api/chats')->assertUnauthorized();
    }

    public function test_chat_messages_returns_paginated_messages_for_chat()
    {
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        $chat = Chat::query()->create([
            'user_one_id' => $userOne->getKey(),
            'user_two_id' => $userTwo->getKey(),
        ]);

        Message::factory(30)->create([
            'chat_id'   => $chat->getKey(),
            'sender_id' => $userOne->getKey(),
            'body'      => 'Hello from user one',
        ]);

        $response = $this->actingAs($userOne)
            ->getJson("/api/chats/{$chat->getKey()}/messages");

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);
        $this->assertCount(25, $response->json('data'));
    }

    public function test_it_returns_chats_in_correct_order()
    {
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        $chat = Chat::query()->create([
            'user_one_id' => $userOne->getKey(),
            'user_two_id' => $userTwo->getKey(),
        ]);

        $newerMessage = Message::factory()->create([
            'chat_id'    => $chat->getKey(),
            'sender_id'  => $userOne->getKey(),
            'body'       => 'Hello from user one',
            'created_at' => Carbon::now(),
        ]);
        $olderMessage = Message::factory()->create([
            'chat_id'    => $chat->getKey(),
            'sender_id'  => $userOne->getKey(),
            'body'       => 'Hello from user one',
            'created_at' => Carbon::now()->subDay(),
        ]);

        $response = $this->actingAs($userOne)
            ->getJson("/api/chats/{$chat->getKey()}/messages");

        $response->assertOk();
        $this->assertEquals($response->json('data')[0]['id'], $newerMessage->getKey());
        $this->assertEquals($response->json('data')[1]['id'], $olderMessage->getKey());
    }

    public function test_chat_messages_returns_not_found_for_invalid_chat()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/chats/not-a-uuid/messages')
            ->assertNotFound();
    }

    public function test_chat_messages_requires_authentication()
    {
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        $chat = Chat::query()->create([
            'user_one_id' => $userOne->getKey(),
            'user_two_id' => $userTwo->getKey(),
        ]);

        $this->getJson("/api/chats/{$chat->getKey()}/messages")
            ->assertUnauthorized();
    }
}
