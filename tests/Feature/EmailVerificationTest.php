<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);
    }

    public function test_unverified_user_cannot_open_dashboard(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('verification.notice'));
    }

    public function test_verified_user_can_open_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    public function test_verification_link_verifies_email(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->get($url)->assertRedirect(route('login'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_expired_or_tampered_link_fails(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->subMinute(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->get($url)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1(uniqid())]);
        $this->get($url)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());

        $other = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $url = str_replace("/verify/{$user->id}/" . sha1($user->email), "/verify/{$other->id}/" . sha1($other->email), $url);
        $this->get($url)->assertForbidden();
        $this->assertFalse($other->fresh()->hasVerifiedEmail());
    }
}
