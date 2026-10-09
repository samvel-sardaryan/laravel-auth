<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolePermissions = [
            'user' => [
                'label' => 'User',
                'permissions' => [
                    'view_dashboard',
                    'create_post',
                ],
            ],
            'moderator' => [
                'label' => 'Moderator',
                'permissions' => [
                    'view_dashboard',
                    'create_post',
                    'view_users',
                    'access_moderator_page',
                    'moderate_comments',
                    'moderate_posts',
                ],
            ],
            'admin' => [
                'label' => 'Admin',
                'permissions' => [
                    'view_dashboard',
                    'create_post',
                    'view_users',
                    'manage_users',
                    'access_moderator_page',
                    'moderate_comments',
                    'moderate_posts',
                    'access_admin_page',
                    'manage_posts',
                    'view_deleted_posts',
                ],
            ],
        ];

        foreach ($rolePermissions as $name => $data) {
            $role = Role::updateOrCreate(
                ['name' => $name],
                ['label' => $data['label']]
            );
            $ids = Permission::whereIn('name', $data['permissions'])->pluck('id');
            $role->permissions()->sync($ids);
        }
    }
}
