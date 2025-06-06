<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_registers_user()
    {
        $response = $this->postJson(route('auth.register'), [
            'name'                  => 'John Doe',
            'email'                 => 'asdf@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'device_name'           => 'test-device',
            'latitude'              => 52.5200,
            'longitude'             => 13.4050,
        ]);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['token']);
    }

    public function test_it_registers_user_with_fcm_token()
    {
        $response = $this->postJson(route('auth.register'), [
            'name'                  => 'Jane Doe',
            'email'                 => 'jane@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'device_name'           => 'jane-device',
            'latitude'              => 40.7128,
            'longitude'             => -74.0060,
            'fcm_token'             => 'sample-token-123',
        ]);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['token']);

        $this->assertDatabaseHas('users', [
            'email'     => 'jane@example.com',
            'fcm_token' => 'sample-token-123',
        ]);
    }
}
