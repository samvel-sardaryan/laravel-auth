<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (empty(config('roles.admin.email')) || empty(config('roles.admin.password'))) {
            throw new RuntimeException("Set admin email and password in .env file");
        }

        $admin = User::updateOrCreate(
            ['email' => config('roles.admin.email')],
            [
                'name' => config('roles.admin.name'),
                'password' => config('roles.admin.password')
            ]
        );

        $admin->email_verified_at = now();
        $admin->save();
        $admin->roles()->sync([
            Role::where('name', 'admin')->value('id')
        ]);
    }
}
