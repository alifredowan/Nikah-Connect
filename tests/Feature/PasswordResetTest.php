<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser(?string $email = null): User
    {
        return User::factory()->create([
            'name' => 'Test Seeker',
            'email' => $email ?? 'user.'.Str::random(8).'@example.com',
            'password' => Hash::make('OldPassword123!'),
            'role' => 'seeker',
            'is_active' => true,
            'is_verified' => true,
        ]);
    }

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertOk();
        $response->assertSee('Forgot Password?');
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = $this->createUser();

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_reset_password_link_fails_for_unknown_email(): void
    {
        $response = $this->post(route('password.email'), [
            'email' => 'nonexistent.'.Str::random(8).'@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = $this->createUser();
        $token = Password::createToken($user);

        $response = $this->get(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]));

        $response->assertOk();
        $response->assertSee('Reset Password');
        $response->assertSee($user->email);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = $this->createUser();
        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        // Verify password is changed
        $this->assertTrue(Hash::check('NewSecurePassword123!', $user->fresh()->password));

        // Verify audit log recorded
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'password_reset',
        ]);
    }

    public function test_password_cannot_be_reset_with_invalid_token(): void
    {
        $user = $this->createUser();

        $response = $this->post(route('password.update'), [
            'token' => 'invalid-token-12345',
            'email' => $user->email,
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertTrue(Hash::check('OldPassword123!', $user->fresh()->password));
    }
}
