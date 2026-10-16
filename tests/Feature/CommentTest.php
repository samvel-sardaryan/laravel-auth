<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\CategorySeeder;

class CommentTest extends TestCase
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

    public function test_reply_to_a_reply_becomes_a_sibling(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();
        $top = Comment::factory()->for($post)->create();
        $reply = Comment::factory()->for($post)->create(['parent_id' => $top->id]);
        $this->actingAs($user)->post(route('comments.store', $post), ['content' => 'Third level', 'parent_id' => $reply->id]);
        $this->assertDatabaseHas('comments', ['content' => 'Third level', 'parent_id' => $top->id]);
    }

    public function test_like_toggles(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('posts.like', $post));
        $this->assertDatabaseHas('post_likes', ['user_id' => $user->id, 'post_id' => $post->id]);
        $this->actingAs($user)->post(route('posts.like', $post));
        $this->assertDatabaseMissing('post_likes', ['user_id' => $user->id, 'post_id' => $post->id]);
    }
}
