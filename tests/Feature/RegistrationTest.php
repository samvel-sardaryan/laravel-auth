<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
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

    public function test_registered_user_gets_the_default_role(): void
    {
        $this->post(route('register'), [
            'name' => 'test',
            'email' => 'test@t.t',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ])->assertRedirect(route('login'));
        $user = User::where('email', 'test@t.t')->first();
        $this->assertTrue($user->hasRole('user'));
    }
}
