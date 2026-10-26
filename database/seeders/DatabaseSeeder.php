<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AdminUserSeeder::class,
            TestUserSeeder::class,
            CategorySeeder::class,
            ContentSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
