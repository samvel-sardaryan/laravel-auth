<?php

namespace Tests\Feature;

use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
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

    public function test_regular_user_gets_403_on_admin(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.index'))->assertForbidden();
    }

    public function test_admin_can_view_users_list(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $other = User::factory()->create();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk()->assertSee($other->email);
    }
}
