<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::updateOrCreate(
            ['name' => 'view_dashboard'],
            ['label' => 'View dashboard']
        );
        Permission::updateOrCreate(
            ['name' => 'create_post'],
            ['label' => 'Write posts']
        );
        Permission::updateOrCreate(
            ['name' => 'view_users'],
            ['label' => 'View users']
        );
        Permission::updateOrCreate(
            ['name' => 'manage_users'],
            ['label' => 'Manage users']
        );
        Permission::updateOrCreate(
            ['name' => 'access_moderator_page'],
            ['label' => 'Open the moderator page']
        );
        Permission::updateOrCreate(
            ['name' => 'moderate_posts'],
            ['label' => 'Hide reported posts']
        );
        Permission::updateOrCreate(
            ['name' => 'moderate_comments'],
            ['label' => 'Hide reported comments']
        );
        Permission::updateOrCreate(
            ['name' => 'access_admin_page'],
            ['label' => 'Open the admin pages']
        );
        Permission::updateOrCreate(
            ['name' => 'manage_posts'],
            ['label' => 'Edit or delete any posts']
        );
        Permission::updateOrCreate(
            ['name' => 'view_deleted_posts'],
            ['label' => 'See or restore deleted posts']
        );
    }
}
