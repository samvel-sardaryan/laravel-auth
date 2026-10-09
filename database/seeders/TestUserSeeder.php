<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(16)->create();
        User::factory()->count(6)->unverified()->create();
        User::factory()->count(4)->withRole('moderator')->create();
        User::factory()->count(2)->unverified()->withRole('moderator')->create();
        User::factory()->count(2)->withRole('admin')->create();
    }
}
