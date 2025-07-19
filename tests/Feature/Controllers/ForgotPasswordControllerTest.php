<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_sends_password_reset_link_to_valid_email()
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'user@example.com',
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);

        $response = $this->postJson(route('auth.forgot-password'), [
            'email' => $user->email,
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Password reset link sent to your email address.',
            ]);

        Mail::assertSent(\Illuminate\Auth\Notifications\ResetPassword::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_it_returns_error_for_invalid_email()
    {
        Mail::fake();

        $response = $this->postJson(route('auth.forgot-password'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);

        Mail::assertNothingSent();
    }

    public function test_it_requires_email_field()
    {
        $response = $this->postJson(route('auth.forgot-password'), []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_requires_valid_email_format()
    {
        $response = $this->postJson(route('auth.forgot-password'), [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_handles_password_reset_throttling()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);

        // First request should succeed
        $this->postJson(route('auth.forgot-password'), [
            'email' => $user->email,
        ])->assertStatus(Response::HTTP_OK);

        // Second request immediately after should be throttled
        $response = $this->postJson(route('auth.forgot-password'), [
            'email' => $user->email,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }
}