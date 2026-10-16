<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;

class ModerationTest extends TestCase
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

    public function test_a_report_is_counted_once(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('reports.post', $post), ['reason' => 'Spam']);
        $this->actingAs($user)->post(route('reports.post', $post), ['reason' => 'Spam']);
        $this->assertDatabaseCount('reports', 1);
    }

    public function test_moderator_hides_a_comment_and_it_is_logged(): void
    {
        $comment = Comment::factory()->create();
        $this->actingAs(User::factory()->create())->delete(route('moderation.comments.hide', $comment))->assertForbidden();
        $moderator = User::factory()->withRole('moderator')->create();
        $this->actingAs($moderator)->delete(route('moderation.comments.hide', $comment))->assertRedirect();
        $this->assertSoftDeleted($comment);
        $this->assertDatabaseHas('activities', ['action' => 'comment.deleted_by_moderator', 'subject_type' => 'comment', 'subject_id' => $comment->id, 'user_id' => $moderator->id]);
    }
}
