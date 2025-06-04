<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_registers_user()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email'       => $user->email,
            'password'    => 'password',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['token']);
    }

    public function test_it_rejects_invalid_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email'       => $user->email,
            'password'    => 'wrong-password',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }
}
