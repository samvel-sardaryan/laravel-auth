<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\RoleSeeder;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->seed([
            RoleSeeder::class,
        ]);
    }

    public function test_forgot_password_sends_notification(): void
    {
        $user = User::factory()->create();
        $response = $this->post(route('password.email'), ['email' => $user->email]);
        $response->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_valid_token_changes_password(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecret123',
            'password_confirmation' => 'NewSecret123',
        ]);
        $response->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('NewSecret123', $user->fresh()->password));
    }

    public function test_invalid_token_fails(): void
    {
        $user = User::factory()->create();
        $response = $this->post(route('password.update'), [
            'token' => 'not-a-real-token',
            'email' => $user->email,
            'password' => 'NewSecret123',
            'password_confirmation' => 'NewSecret123',
        ]);
        $response->assertSessionHasErrors('email');
    }

    public function test_unknown_email_fails(): void
    {
        Notification::fake();
        $response = $this->post(route('password.email'), ['email' => 'unknown@t.t']);
        $response->assertSessionHas('status');
        Notification::assertNothingSent();
    }
}
