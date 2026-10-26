<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

final class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $tags = collect(['php', 'laravel', 'mysql', 'css', 'testing'])->map(fn ($name) => Tag::firstOrCreate(['name' => $name]));
        Post::factory()->count(40)->recycle($users)->create()
            ->each(function (Post $post) use ($users, $tags) {
                $post->tags()->attach($tags->random(rand(0, 3))->pluck('id'));
                $post->likers()->attach($users->random(rand(0, 5))->pluck('id'));
                Comment::factory()->count(rand(0, 4))->for($post)->recycle($users)->create();
            });
    }
}
