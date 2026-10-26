<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
            CategorySeeder::class,
        ]);
    }

    public function test_profile_page_opens(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('profile.edit'))->assertOk();
    }

    public function test_deleting_a_missing_avatar_says_so(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete(route('profile.avatar.destroy'))->assertSessionHas('error', 'No avatar to delete');
    }
}
