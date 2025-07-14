<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ResetPasswordControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_resets_password_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('oldpassword'),
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson(route('auth.reset-password'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Password has been reset successfully.',
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_it_rejects_invalid_token()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);

        $response = $this->postJson(route('auth.reset-password'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_rejects_expired_token()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);

        $token = Password::createToken($user);

        // Simulate token expiration by manipulating the database
        \DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->update(['created_at' => now()->subHours(2)]);

        $response = $this->postJson(route('auth.reset-password'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_rejects_mismatched_email()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson(route('auth.reset-password'), [
            'token' => $token,
            'email' => 'wrong@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_requires_all_fields()
    {
        $response = $this->postJson(route('auth.reset-password'), []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['token', 'email', 'password']);
    }

    public function test_it_requires_valid_email_format()
    {
        $response = $this->postJson(route('auth.reset-password'), [
            'token' => 'some-token',
            'email' => 'invalid-email',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_requires_minimum_password_length()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson(route('auth.reset-password'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_it_requires_password_confirmation()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson(route('auth.reset-password'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_it_deletes_token_after_successful_reset()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);

        $token = Password::createToken($user);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);

        $this->postJson(route('auth.reset-password'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }
}