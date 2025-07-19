<?php

namespace Tests\Feature;

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_complete_password_reset_flow()
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('oldpassword'),
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);

        // Step 1: Request password reset
        $response = $this->postJson(route('auth.forgot-password'), [
            'email' => $user->email,
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Password reset link sent to your email address.',
            ]);

        // Verify email was sent
        Mail::assertSent(\Illuminate\Auth\Notifications\ResetPassword::class);

        // Step 2: Get the token from the database (simulating clicking email link)
        $tokenRecord = \DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->first();

        $this->assertNotNull($tokenRecord);

        // Step 3: Reset password using the token
        $response = $this->postJson(route('auth.reset-password'), [
            'token' => $tokenRecord->token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Password has been reset successfully.',
            ]);

        // Step 4: Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
        $this->assertFalse(Hash::check('oldpassword', $user->password));

        // Step 5: Verify token was deleted
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);

        // Step 6: Verify user can login with new password
        $loginResponse = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'newpassword123',
            'device_name' => 'test-device',
        ]);

        $loginResponse->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['token', 'user_id']);
    }

    public function test_password_reset_web_route_returns_token_info()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);

        $token = Password::createToken($user);

        $response = $this->get("/password/reset/{$token}?email={$user->email}");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Use this token with your Flutter app',
                'token' => $token,
                'instructions' => 'Send a POST request to /api/auth/reset-password with this token, email, password, and password_confirmation'
            ]);
    }

    public function test_cannot_reuse_token_after_successful_reset()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('oldpassword'),
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);

        $token = Password::createToken($user);

        // First reset - should succeed
        $response = $this->postJson(route('auth.reset-password'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(Response::HTTP_OK);

        // Second reset with same token - should fail
        $response = $this->postJson(route('auth.reset-password'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'anotherpassword123',
            'password_confirmation' => 'anotherpassword123',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_multiple_users_can_reset_passwords_simultaneously()
    {
        $user1 = User::factory()->create([
            'email' => 'user1@example.com',
            'password' => Hash::make('oldpassword1'),
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);

        $user2 = User::factory()->create([
            'email' => 'user2@example.com',
            'password' => Hash::make('oldpassword2'),
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);

        $token1 = Password::createToken($user1);
        $token2 = Password::createToken($user2);

        // Reset user1's password
        $response1 = $this->postJson(route('auth.reset-password'), [
            'token' => $token1,
            'email' => $user1->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        // Reset user2's password
        $response2 = $this->postJson(route('auth.reset-password'), [
            'token' => $token2,
            'email' => $user2->email,
            'password' => 'anotherpassword123',
            'password_confirmation' => 'anotherpassword123',
        ]);

        $response1->assertStatus(Response::HTTP_OK);
        $response2->assertStatus(Response::HTTP_OK);

        // Verify both passwords were changed
        $user1->refresh();
        $user2->refresh();
        
        $this->assertTrue(Hash::check('newpassword123', $user1->password));
        $this->assertTrue(Hash::check('anotherpassword123', $user2->password));
    }
}