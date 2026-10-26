<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RoleChangeTest extends TestCase
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

    public function test_admin_can_change_another_users_role(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $target = User::factory()->create();
        $moderator = Role::where('name', 'moderator')->first();
        $response = $this->actingAs($admin)->patch(route('admin.users.role.update', $target), ['role_id' => $moderator->id]);
        $response->assertRedirect();
        $this->assertTrue($target->fresh()->hasRole('moderator'));
        $this->assertFalse($target->fresh()->hasRole('user'));
    }

    public function test_regular_user_gets_403_on_role_change(): void
    {
        $target = User::factory()->create();
        $user = User::factory()->create();
        $moderator = Role::where('name', 'moderator')->first();
        $response = $this->actingAs($user)->patch(route('admin.users.role.update', $target), ['role_id' => $moderator->id]);
        $response->assertForbidden();
        $this->assertTrue($target->fresh()->hasRole('user'));
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $moderator = Role::where('name', 'moderator')->first();
        $response = $this->actingAs($admin)->patch(route('admin.users.role.update', $admin), ['role_id' => $moderator->id]);
        $response->assertForbidden();
        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }
}
