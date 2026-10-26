<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PostTest extends TestCase
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

    public function test_only_owner_or_permission_manage_posts_can_edit(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner)->create();
        $this->actingAs(User::factory()->create())->get(route('posts.edit', $post))->assertForbidden();
        $this->actingAs(User::factory()->withRole('admin')->create())->get(route('posts.edit', $post))->assertOk();
        $this->actingAs($owner)->get(route('posts.edit', $post))->assertOk();
    }

    public function test_draft_is_hidden_from_others_but_not_its_author(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->draft()->for($author)->create();
        $this->get(route('posts.show', $post))->assertNotFound();
        $this->actingAs(User::factory()->create())->get(route('posts.show', $post))->assertNotFound();
        $this->actingAs($author)->get(route('posts.show', $post))->assertOk();
    }

    public function test_deleted_post_is_404_even_for_its_author(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $post->delete();
        $this->actingAs($author)->get(route('posts.show', $post))->assertNotFound();
    }
}
