<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::updateOrCreate(
            ['slug' => 'news'],
            [
                'name' => 'News',
                'is_default' => false,
            ]
        );
        Category::updateOrCreate(
            ['slug' => 'tutorial'],
            [
                'name' => 'Tutorial',
                'is_default' => false,
            ]
        );
        Category::updateOrCreate(
            ['slug' => 'question'],
            [
                'name' => 'Question',
                'is_default' => false,
            ]
        );
        Category::updateOrCreate(
            ['slug' => 'other'],
            [
                'name' => 'Other',
                'is_default' => true,
            ]
        );
    }
}
