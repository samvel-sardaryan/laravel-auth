<?php

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class DemoSeeder extends Seeder
{
    private const MARKER = 'Why I finally switched from plain PHP to Laravel';

    /** @var array<int, array<string, mixed>> */
    private array $activities = [];

    /** @var array<int, Carbon> */
    private array $joined = [];

    private ?string $font = null;

    private Carbon $now;

    public function run(): void
    {
        if (Post::withTrashed()->where('title', self::MARKER)->exists()) {
            $this->command?->warn('Demo content is already there, skipping.');

            return;
        }

        $this->now = Carbon::now();
        $this->font = collect([
            'C:\Windows\Fonts\segoeuib.ttf',
            'C:\Windows\Fonts\arialbd.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        ])->first(fn (string $path) => is_file($path));
        $this->removeOldDemoFiles();

        $admin = User::where('email', config('roles.admin.email'))->firstOrFail();
        $users = User::with('roles')->orderBy('id')->get();
        $verified = $users->whereNotNull('email_verified_at')->values();
        $moderators = $users->filter(fn (User $user) => $user->hasRole('moderator'))->values();

        $this->spreadUserDates($users, $admin);
        $this->spreadFactoryContentDates();
        $this->addProfiles($verified, $admin);
        $this->logRoleChanges($users, $admin);
        $posts = $this->addPosts($verified, $admin);
        $this->addComments($posts, $verified);
        $this->addLikes($posts, $verified);
        $this->addModeration($posts, $verified, $moderators->isNotEmpty() ? $moderators : collect([$admin]), $admin);
        $this->addLoginNoise($users);
        $this->flushActivities();
        $this->addDeletedUser();

        $this->command?->info('Demo content seeded.');
    }

    private function removeOldDemoFiles(): void
    {
        $disk = Storage::disk('public');
        foreach (['posts', 'avatars'] as $directory) {
            $old = array_filter($disk->files($directory), fn (string $path) => str_starts_with(basename($path), 'demo-'));
            $disk->delete($old);
        }
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function spreadUserDates(Collection $users, User $admin): void
    {
        foreach ($users as $user) {
            $joined = $user->is($admin)
                ? $this->now->copy()->subDays(200)
                : $this->now->copy()->subDays(rand(45, 150))->subMinutes(rand(0, 1440));
            $this->joined[$user->id] = $joined;

            DB::table('users')->where('id', $user->id)->update([
                'created_at' => $joined,
                'updated_at' => $joined,
                'email_verified_at' => $user->email_verified_at ? $joined->copy()->addMinutes(rand(3, 240)) : null,
            ]);
        }
    }

    private function spreadFactoryContentDates(): void
    {
        foreach (Post::withTrashed()->get() as $post) {
            $earliest = ($this->joined[$post->user_id] ?? $this->now->copy()->subDays(100))->copy()->addDay();
            $latest = $this->now->copy()->subDays(31);
            $created = $earliest->lt($latest)
                ? Carbon::createFromTimestamp(rand($earliest->timestamp, $latest->timestamp))
                : $latest;

            DB::table('posts')->where('id', $post->id)->update(['created_at' => $created, 'updated_at' => $created]);
            $this->log('post.created', $post->user_id, 'post', $post->id, $created);

            foreach (Comment::withTrashed()->where('post_id', $post->id)->get() as $comment) {
                $at = $this->after($created, 30, 60 * 24 * 20);
                DB::table('comments')->where('id', $comment->id)->update(['created_at' => $at, 'updated_at' => $at]);
            }
        }
    }

    /**
     * @param  Collection<int, User>  $verified
     */
    private function addProfiles(Collection $verified, User $admin): void
    {
        $locations = ['Yerevan, Armenia', 'Gyumri, Armenia', 'Tbilisi, Georgia', 'Berlin, Germany', 'Lisbon, Portugal', 'Toronto, Canada', 'Austin, USA', 'Warsaw, Poland', 'Prague, Czechia', 'Barcelona, Spain', 'Remote'];
        $bios = [
            "Backend developer, mostly PHP and MySQL.\nLearning Laravel one feature at a time.",
            'Frontend person who wandered into the backend and liked it.',
            'Student. Building small projects to understand how the web really works.',
            "I write tests before I write code. Most of the time.\nCoffee first, then everything else.",
            'Ten years of PHP, two of them with Laravel. Ask me about queues.',
            'Moderator here. Be kind, stay on topic.',
            'Designer learning to code. CSS grid changed my life.',
            'Open source fan. Currently reading the Laravel source code for fun.',
            'Freelancer. I build shops, blogs and the occasional dashboard.',
            'Self-taught developer from Gyumri. Always happy to help beginners.',
            'Database nerd. If your query is slow, show me the EXPLAIN.',
            'QA engineer who got tired of finding the same bugs.',
        ];
        $palettes = $this->palettes();

        $people = $verified->reject(fn (User $user) => $user->is($admin))->shuffle()->take(22)->push($admin);
        foreach ($people->values() as $index => $user) {
            [$first, $last] = $this->splitName($user->name);
            $full = $index % 4 !== 3;
            $details = [
                'first_name' => $first,
                'last_name' => $last,
                'location' => $locations[array_rand($locations)],
                'bio' => $bios[$index % count($bios)],
                'phone' => $full ? '+374 '.rand(10, 99).' '.rand(100, 999).' '.rand(100, 999) : null,
                'date_of_birth' => $full ? $this->now->copy()->subYears(rand(19, 45))->subDays(rand(0, 364))->toDateString() : null,
            ];
            if ($index % 5 === 4) {
                $details = ['bio' => $details['bio'], 'location' => $details['location']];
            }
            if ($index < 14 || $user->is($admin)) {
                [$from, $to] = $palettes[$index % count($palettes)];
                $initials = mb_strtoupper(mb_substr($first, 0, 1).mb_substr($last, 0, 1));
                $details['avatar'] = $this->makeImage('avatars', 256, 256, $from, $to, $initials, 72);
            }
            $user->profile()->updateOrCreate([], $details);
        }
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function logRoleChanges(Collection $users, User $admin): void
    {
        foreach ($users as $user) {
            if ($user->is($admin) || ! $user->hasAnyRole(['moderator', 'admin'])) {
                continue;
            }
            $this->log('role.changed', $admin->id, 'user', $user->id, $this->after($this->joined[$user->id], 60, 60 * 24 * 10));
        }
    }

    /**
     * @param  Collection<int, User>  $verified
     * @return array<string, Post>
     */
    private function addPosts(Collection $verified, User $admin): array
    {
        $authors = $verified->reject(fn (User $user) => $user->is($admin))->shuffle()->values();
        $categories = Category::pluck('id', 'slug');
        $palettes = $this->palettes();
        $posts = [];

        foreach ($this->postData() as $index => $data) {
            $author = ($data['admin'] ?? false) ? $admin : $authors[$index % $authors->count()];
            $created = $this->now->copy()->subMinutes((int) round($data['days'] * 1440) + rand(0, 120));
            $created = $created->max($this->joined[$author->id]->copy()->addDay());

            $post = new Post([
                'title' => $data['title'],
                'content' => $data['content'],
                'status' => $data['status'] ?? PostStatus::Published,
                'category_id' => $categories[$data['category']] ?? $categories->first(),
            ]);
            $post->user()->associate($author);
            $post->created_at = $created;
            $post->updated_at = $created;
            $post->save();

            $post->tags()->sync(collect($data['tags'] ?? [])->map(fn (string $name) => Tag::firstOrCreate(['name' => $name])->id)->all());
            foreach ($data['images'] ?? [] as $i => $caption) {
                [$from, $to] = $palettes[($index + $i) % count($palettes)];
                $post->images()->create(['path' => $this->makeImage('posts', 800, 450, $from, $to, $caption, 38)]);
            }
            $this->log('post.created', $author->id, 'post', $post->id, $created);

            if ($index % 3 === 1 && $post->status === PostStatus::Published) {
                $edited = $this->after($created, 20, 60 * 30);
                DB::table('posts')->where('id', $post->id)->update(['updated_at' => $edited]);
                $this->log('post.updated', $author->id, 'post', $post->id, $edited);
            }

            $posts[$data['key'] ?? 'post'.$index] = $post;
        }

        return $posts;
    }

    /**
     * @param  array<string, Post>  $posts
     * @param  Collection<int, User>  $verified
     */
    private function addComments(array $posts, Collection $verified): void
    {
        $tops = [
            'This is exactly the problem I had last week. Thanks for writing it up!',
            'Great explanation. Could you add an example with pagination?',
            'I tried this and it cut my page load in half.',
            'Do you have the code on GitHub?',
            'Small correction: you need to restart the queue worker after changing the code.',
            'I disagree a little. For small projects plain PHP is still fine.',
            'Bookmarked. The part about transactions is gold.',
            'How does this compare to doing it by hand with PDO?',
            'We use the same approach at work and it scales well.',
            'Nice! The screenshots really help.',
            'What about testing this? Would love a follow-up post.',
            'I always forget the withTrashed() part, thanks for the reminder.',
            "Clear and short, that's how tutorials should be.",
            'Is this still valid for older Laravel versions?',
            'Had no idea this was built in. Deleting my custom code now.',
            "The second example didn't work for me until I cleared the config cache.",
            'Thanks, this answered my question from yesterday.',
            '+1, I ran into the same issue.',
        ];
        $replies = [
            "Yes, I'll push it this weekend.",
            "Good point, I'll update the post.",
            'Same here, it took me hours to find.',
            'It works on 12 too, I just tested it.',
            'Try php artisan config:clear first.',
            "Agreed, for a tiny site it's overkill.",
            'Thanks! Glad it helped.',
            "I'll write the follow-up next week.",
            "That's a fair point.",
            'Exactly, the query count tells the whole story.',
        ];

        foreach ($posts as $key => $post) {
            if ($post->status !== PostStatus::Published || in_array($key, ['spam', 'followers', 'ignore', 'wrong-account'], true)) {
                continue;
            }
            $topCount = $key === 'switch' ? 6 : rand(1, 5);
            for ($t = 0; $t < $topCount; $t++) {
                $top = $this->comment($post, $verified->random(), $tops[array_rand($tops)], null, $this->after($post->created_at, 30, 60 * 24 * 5));
                if (rand(0, 1) === 1) {
                    $previous = $top;
                    for ($r = 0, $n = rand(1, 3); $r < $n; $r++) {
                        $mention = $previous->is($top) ? '' : '@'.$previous->user->name.' ';
                        $previous = $this->comment($post, $verified->random(), $mention.$replies[array_rand($replies)], $top->id, $this->after($previous->created_at, 10, 60 * 24 * 2));
                    }
                }
            }
        }

        $switch = $posts['switch'];
        $gone = $this->comment($switch, $verified->random(), 'Posting my email so you can contact me about a job.', null, $this->after($switch->created_at, 60, 600));
        $warning = $this->comment($switch, $verified->random(), 'Careful, bots read these pages. Maybe remove it?', $gone->id, $this->after($gone->created_at, 10, 120));
        $this->comment($switch, $gone->user, '@'.$warning->user->name.' Good call, removing it now.', $gone->id, $this->after($gone->created_at, 130, 200));
        $deletedAt = $this->after($gone->created_at, 210, 300);
        DB::table('comments')->where('id', $gone->id)->update(['deleted_at' => $deletedAt]);
        $this->log('comment.deleted', $gone->user_id, 'comment', $gone->id, $deletedAt);
    }

    /**
     * @param  array<string, Post>  $posts
     * @param  Collection<int, User>  $verified
     */
    private function addLikes(array $posts, Collection $verified): void
    {
        foreach ($posts as $key => $post) {
            if ($post->status !== PostStatus::Published) {
                continue;
            }
            $count = in_array($key, ['switch', 'eager', 'queues'], true) ? rand(12, 18) : rand(0, 9);
            $likers = $verified->shuffle()->take($count);
            DB::table('post_likes')->insertOrIgnore($likers->map(fn (User $user) => [
                'user_id' => $user->id,
                'post_id' => $post->id,
                'created_at' => $this->after($post->created_at, 5, 60 * 24 * 7),
            ])->all());
        }
    }

    /**
     * @param  array<string, Post>  $posts
     * @param  Collection<int, User>  $verified
     * @param  Collection<int, User>  $moderators
     */
    private function addModeration(array $posts, Collection $verified, Collection $moderators, User $admin): void
    {
        $reporters = fn (Post|Comment $target, int $count) => $verified->reject(fn (User $user) => $user->id === $target->user_id)->shuffle()->take($count);
        $report = function (Post|Comment $target, array $reasons) use ($reporters): Carbon {
            $last = $target->created_at;
            foreach ($reporters($target, count($reasons))->values() as $i => $user) {
                $last = $this->after($last, 30, 60 * 24);
                $row = new Report(['reporter_id' => $user->id, 'reason' => $reasons[$i]]);
                $row->reportable()->associate($target);
                $row->created_at = $last;
                $row->updated_at = $last;
                $row->save();
            }

            return $last;
        };
        $hidePost = function (Post $post, Carbon $after) use ($moderators): void {
            $at = $this->after($after, 60, 60 * 12);
            DB::table('posts')->where('id', $post->id)->update(['deleted_at' => $at]);
            $this->log('post.deleted_by_moderator', $moderators->random()->id, 'post', $post->id, $at);
        };

        $hidePost($posts['spam'], $report($posts['spam'], ['Spam', 'Looks like a scam', 'Advertising']));
        $report($posts['followers'], ['Spam', 'Selling fake followers']);
        $report($posts['pizza'], ['Off-topic: this forum is about programming']);
        $hidePost($posts['meetup'], $report($posts['meetup'], ['Photos of people posted without asking them']));

        $restored = $posts['tailwind'];
        $reported = $report($restored, ['Looks like spam to me']);
        $hidePost($restored, $reported);
        $restoredAt = $this->after($reported, 60 * 14, 60 * 30);
        DB::table('posts')->where('id', $restored->id)->update(['deleted_at' => null]);
        $this->log('post.restored', $admin->id, 'post', $restored->id, $restoredAt);

        foreach (['ignore', 'wrong-account'] as $key) {
            $at = $this->after($posts[$key]->created_at, 3, 40);
            DB::table('posts')->where('id', $posts[$key]->id)->update(['deleted_at' => $at]);
            $this->log('post.deleted', $posts[$key]->user_id, 'post', $posts[$key]->id, $at);
        }

        $question = $posts['validation'];
        $rude = $this->comment($question, $verified->random(), 'This is a stupid question, just google it.', null, $this->after($question->created_at, 30, 300));
        $at = $this->after($report($rude, ['Rude', 'Not helpful and insulting']), 60, 600);
        DB::table('comments')->where('id', $rude->id)->update(['deleted_at' => $at]);
        $this->log('comment.deleted_by_moderator', $moderators->random()->id, 'comment', $rude->id, $at);

        $images = $posts['images-in-db'];
        $report($this->comment($images, $verified->random(), 'Check my profile for free crypto!!!', null, $this->after($images->created_at, 60, 600)), ['Spam', 'Scam link']);
        $report($this->comment($posts['rate-limit'], $verified->random(), 'Storing passwords as md5 is totally fine, nobody can reverse it.', null, $this->after($posts['rate-limit']->created_at, 60, 900)), ['Dangerous advice']);
    }

    /**
     * Failed logins, lockouts and password resets over the last two months.
     *
     * @param  Collection<int, User>  $users
     */
    private function addLoginNoise(Collection $users): void
    {
        $ips = ['203.0.113.'.rand(2, 250), '198.51.100.'.rand(2, 250), '192.0.2.'.rand(2, 250), '45.83.12.7', '91.204.188.14', '185.28.111.40', '37.252.66.3', '5.77.31.198', '94.43.120.6', '212.42.198.77'];
        for ($i = 0; $i < 470; $i++) {
            $at = $this->now->copy()->subMinutes(rand(10, 60 * 24 * 60));
            $user = rand(1, 100) <= 85 ? $users->random() : null;
            $this->log('login.failed', null, $user ? 'user' : null, $user?->id, $at, $ips[array_rand($ips)]);
        }
        for ($i = 0; $i < 18; $i++) {
            $this->log('login.blocked', null, null, null, $this->now->copy()->subMinutes(rand(10, 60 * 24 * 60)), $ips[array_rand($ips)]);
        }
        foreach ($users->shuffle()->take(8) as $user) {
            $this->log('password.reset', $user->id, null, null, $this->after($this->joined[$user->id], 60 * 24, 60 * 24 * 40));
        }
    }

    /**
     * A member who posted, then deleted their account: their log rows say "deleted user".
     */
    private function addDeletedUser(): void
    {
        $former = User::factory()->create(['name' => 'Former Member', 'email' => 'former-member-'.Str::random(6).'@example.com']);
        $joined = $this->now->copy()->subDays(70);
        $post = new Post(['title' => 'Hello from a soon-to-leave member', 'content' => 'Just testing the forum.', 'status' => PostStatus::Published, 'category_id' => Category::value('id')]);
        $post->user()->associate($former);
        $post->save();

        foreach ([['post.created', 'post', $post->id, 1], ['post.updated', 'post', $post->id, 2], ['password.reset', null, null, 9]] as [$action, $type, $id, $days]) {
            DB::table('activities')->insert([
                'user_id' => $former->id,
                'action' => $action,
                'subject_type' => $type,
                'subject_id' => $id,
                'ip_address' => '88.198.41.12',
                'created_at' => $joined->copy()->addDays($days),
                'updated_at' => $joined->copy()->addDays($days),
            ]);
        }
        $former->delete();
    }

    private function comment(Post $post, User $author, string $content, ?int $parentId, Carbon $at): Comment
    {
        $comment = new Comment(['content' => $content, 'parent_id' => $parentId]);
        $comment->user()->associate($author);
        $comment->post()->associate($post);
        $comment->created_at = $at;
        $comment->updated_at = rand(1, 6) === 1 ? $this->after($at, 5, 90) : $at;
        $comment->save();

        return $comment;
    }

    private function log(string $action, ?int $userId, ?string $subjectType, ?int $subjectId, Carbon $at, ?string $ip = null): void
    {
        $this->activities[] = [
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'ip_address' => $ip ?? ($userId ? '10.0.'.($userId % 250).'.'.(($userId * 7) % 250 + 2) : '127.0.0.1'),
            'created_at' => $at,
            'updated_at' => $at,
        ];
    }

    private function flushActivities(): void
    {
        usort($this->activities, fn (array $a, array $b) => $a['created_at'] <=> $b['created_at']);
        foreach (array_chunk($this->activities, 200) as $chunk) {
            DB::table('activities')->insert($chunk);
        }
        $this->activities = [];
    }

    /**
     * A random moment between $min and $max minutes after $base, never in the future.
     */
    private function after(Carbon $base, int $min, int $max): Carbon
    {
        return $base->copy()->addMinutes(rand($min, $max))->min($this->now->copy()->subMinute());
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $parts = array_values(array_filter(explode(' ', $name), fn (string $part) => ! str_ends_with($part, '.') && ! in_array($part, ['Jr', 'Sr', 'II', 'III', 'IV', 'V', 'MD', 'PhD', 'DVM', 'DDS'], true)));

        return [$parts[0] ?? $name, count($parts) > 1 ? $parts[count($parts) - 1] : ''];
    }

    /**
     * Draws a vertical gradient with centred white text and stores it on the public disk.
     *
     * @param  array{0: int, 1: int, 2: int}  $from
     * @param  array{0: int, 1: int, 2: int}  $to
     */
    private function makeImage(string $directory, int $width, int $height, array $from, array $to, string $text, int $size): string
    {
        $image = imagecreatetruecolor($width, $height);
        for ($y = 0; $y < $height; $y++) {
            $t = $y / max(1, $height - 1);
            $color = imagecolorallocate($image, (int) ($from[0] + ($to[0] - $from[0]) * $t), (int) ($from[1] + ($to[1] - $from[1]) * $t), (int) ($from[2] + ($to[2] - $from[2]) * $t));
            imageline($image, 0, $y, $width, $y, $color);
        }

        $white = imagecolorallocate($image, 255, 255, 255);
        if ($this->font === null) {
            imagestring($image, 5, (int) max(4, ($width - imagefontwidth(5) * strlen($text)) / 2), (int) ($height / 2 - 8), $text, $white);
        } else {
            $shadow = imagecolorallocatealpha($image, 0, 0, 0, 90);
            $lines = [];
            $line = '';
            foreach (explode(' ', $text) as $word) {
                $candidate = trim($line.' '.$word);
                $box = imagettfbbox($size, 0, $this->font, $candidate);
                if ($line !== '' && $width * 0.84 < $box[2] - $box[0]) {
                    $lines[] = $line;
                    $line = $word;
                } else {
                    $line = $candidate;
                }
            }
            $lines[] = $line;
            $lineHeight = (int) ($size * 1.45);
            $y = (int) (($height - $lineHeight * count($lines)) / 2 + $size);
            foreach ($lines as $current) {
                $box = imagettfbbox($size, 0, $this->font, $current);
                $x = (int) (($width - ($box[2] - $box[0])) / 2);
                imagettftext($image, $size, 0, $x + 2, $y + 2, $shadow, $this->font, $current);
                imagettftext($image, $size, 0, $x, $y, $white, $this->font, $current);
                $y += $lineHeight;
            }
        }

        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();

        $path = $directory.'/demo-'.Str::random(32).'.png';
        Storage::disk('public')->put($path, $png);

        return $path;
    }

    /**
     * @return list<array{0: array{0: int, 1: int, 2: int}, 1: array{0: int, 1: int, 2: int}}>
     */
    private function palettes(): array
    {
        return [
            [[79, 70, 229], [147, 51, 234]],
            [[14, 116, 144], [34, 197, 94]],
            [[234, 88, 12], [219, 39, 119]],
            [[30, 64, 175], [56, 189, 248]],
            [[22, 101, 52], [132, 204, 22]],
            [[159, 18, 57], [249, 115, 22]],
            [[71, 85, 105], [148, 163, 184]],
            [[124, 58, 237], [236, 72, 153]],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function postData(): array
    {
        return [
            [
                'key' => 'switch',
                'days' => 2.1,
                'title' => self::MARKER,
                'category' => 'other',
                'tags' => ['php', 'laravel', 'career'],
                'content' => "For a year I built everything by hand: my own router, my own CSRF tokens, my own login throttling with a table of failed attempts. It taught me a lot, and I don't regret it.\n\nThen I rebuilt the same app in Laravel. Email verification became a signed URL. Roles and permissions became one Gate::before callback. Soft deletes replaced a dozen WHERE deleted_at IS NULL clauses.\n\nThe best part: because I had built it all myself first, I actually understand what Laravel does for me.",
            ],
            [
                'key' => 'eager',
                'days' => 0.3,
                'title' => 'Eloquent eager loading: from 23 queries to 9',
                'category' => 'tutorial',
                'tags' => ['laravel', 'eloquent', 'performance'],
                'images' => ['Without with(): 23 queries', 'With with(\'roles\'): 9 queries'],
                'content' => "My admin users page listed 15 users with their roles. Debugbar showed 23 queries. Each row loaded its roles separately: the classic N+1 problem.\n\nAdding ->with('roles') to the query loads the roles of all 15 users in one extra query. The page dropped to 9 queries, and the number no longer grows with the page size.\n\nRule of thumb: if you use a relation inside a loop in Blade, eager load it in the controller.",
            ],
            [
                'key' => 'validation',
                'days' => 1.2,
                'title' => 'How do you structure validation for big forms?',
                'category' => 'question',
                'tags' => ['laravel', 'php'],
                'content' => "I have a post form with title, content, category, tags and up to five images. The rules array is getting long, and one rule needs to look at the post being edited (the image count).\n\nDo you keep everything in one Form Request? Or split it? I'm using the after() hook for the image count right now and it works, but I'm not sure it's the right place.",
            ],
            [
                'key' => 'release',
                'days' => 6,
                'title' => 'Laravel 13 is out: what changed for everyday code',
                'category' => 'news',
                'tags' => ['laravel', 'php'],
                'images' => ['Laravel 13'],
                'content' => "The new release keeps the slim application skeleton from the last versions: no Kernel classes, middleware configured in bootstrap/app.php, and attributes like #[Fillable] and #[ObservedBy] on models.\n\nIf you're upgrading a small project, read the upgrade guide, run your tests, and check any package that hooks into the HTTP kernel.",
            ],
            [
                'key' => 'soft-deletes',
                'days' => 9,
                'title' => 'Soft deletes explained with a real example',
                'category' => 'tutorial',
                'tags' => ['laravel', 'eloquent', 'mysql'],
                'content' => "Add the SoftDeletes trait and a deleted_at column, and delete() stops removing the row. Every query, relation and route model binding then skips deleted rows by itself.\n\nTo show deleted posts on an admin page, use Post::onlyTrashed(). To restore one, call restore(). Remember ->withTrashed() on the restore route, or route model binding answers 404.",
            ],
            [
                'key' => 'grid',
                'days' => 4,
                'title' => 'CSS grid layout for a blog feed in 20 lines',
                'category' => 'tutorial',
                'tags' => ['css'],
                'images' => ['Grid: three columns', 'Grid: one column on phones'],
                'content' => "display: grid with grid-template-columns: repeat(auto-fill, minmax(18rem, 1fr)) gives you a responsive feed without a single media query.\n\nAdd gap: 1.5rem and you're done. The cards wrap to one column on phones automatically.",
            ],
            [
                'key' => 'images-in-db',
                'days' => 3,
                'title' => 'Is it okay to store images in the database?',
                'category' => 'question',
                'tags' => ['mysql', 'php'],
                'content' => "A friend stores uploaded images as BLOBs in MySQL. I store them on disk and keep only the path in the database.\n\nWhich one is better for a small forum? Backups are easier with everything in the database, but the table gets huge.",
            ],
            [
                'key' => 'meetup',
                'days' => 12,
                'title' => 'Photos from the Yerevan PHP meetup',
                'category' => 'news',
                'tags' => ['php', 'career'],
                'images' => ['Yerevan PHP meetup: opening talk', 'Live coding: building a policy', 'Questions and answers'],
                'content' => "Thanks to everyone who came! Forty people, three talks, and a lot of good questions about queues and testing.\n\nSlides will follow next week. Here are a few photos from the evening.",
            ],
            [
                'key' => 'first-test',
                'days' => 5,
                'title' => 'Writing my first feature test with PHPUnit',
                'category' => 'tutorial',
                'tags' => ['testing', 'laravel'],
                'content' => "Start with the smallest useful test: a guest who opens /dashboard should be redirected to /login.\n\nUse RefreshDatabase so every test starts with empty tables, and remember that test methods must be public and start with test_, or PHPUnit silently skips them. I learned that one the hard way.",
            ],
            [
                'key' => 'rate-limit',
                'days' => 7.5,
                'title' => 'Rate limiting logins without a table',
                'category' => 'tutorial',
                'tags' => ['security', 'laravel'],
                'images' => ['5 attempts per 15 minutes'],
                'content' => "In my plain PHP version I counted failed logins in a login_attempts table and cleaned it up with a cron job.\n\nLaravel's RateLimiter keeps the counter in the cache with an expiry. Key it on email and IP together, so an attacker can't lock a real user out from another computer.",
            ],
            [
                'key' => 'indexes',
                'days' => 10,
                'title' => 'MySQL indexes: when do they actually help?',
                'category' => 'question',
                'tags' => ['mysql', 'performance'],
                'content' => "I added an index on (status, deleted_at, created_at) for my posts feed. EXPLAIN says it's used, but with 40 rows I can't measure any difference.\n\nAt what table size do indexes start to matter?",
            ],
            [
                'key' => 'git',
                'days' => 13,
                'title' => 'Git branches for solo projects: worth it?',
                'category' => 'question',
                'tags' => ['git', 'career'],
                'content' => "I work alone and commit straight to main. A mentor told me to use a branch per feature anyway.\n\nDo you do that on personal projects? It feels like extra ceremony.",
            ],
            [
                'key' => 'signed',
                'days' => 15,
                'title' => 'Signed URLs are the most underrated Laravel feature',
                'category' => 'other',
                'tags' => ['laravel', 'security'],
                'content' => "A signed URL carries its own expiry and an HMAC of the whole address, keyed with APP_KEY. Change one character and it fails.\n\nThat's why email verification needs no token column: the server can recompute the signature at any time.",
            ],
            [
                'key' => 'queues',
                'days' => 0.8,
                'title' => 'Queues for email: sync vs database driver',
                'category' => 'tutorial',
                'tags' => ['laravel', 'performance'],
                'images' => ['sync: the user waits', 'database: a worker sends it'],
                'content' => "With the sync driver, the registration page waits while the mail server answers. With the database driver, the request only writes a row to the jobs table and answers at once; php artisan queue:work sends the email in the background.\n\nDon't forget to restart the worker after changing code. It keeps the old code in memory.",
            ],
            [
                'key' => 'pizza',
                'days' => 11,
                'title' => 'Best pizza near the office?',
                'category' => 'question',
                'tags' => [],
                'content' => 'We are ordering for Friday. Any recommendations?',
            ],
            [
                'key' => 'spam',
                'days' => 14,
                'title' => 'Earn $5000 a week from home!!!',
                'category' => 'other',
                'tags' => [],
                'content' => 'No experience needed. Click the link in my profile to start today. Limited spots!!!',
            ],
            [
                'key' => 'followers',
                'days' => 8.5,
                'title' => 'Cheap followers and likes, DM me',
                'category' => 'other',
                'tags' => [],
                'content' => '1000 followers for 5 dollars. Real accounts. Fast delivery.',
            ],
            [
                'key' => 'tailwind',
                'days' => 16,
                'title' => 'Tailwind or plain CSS for a small project?',
                'category' => 'question',
                'tags' => ['css'],
                'images' => ['Plain CSS, one file'],
                'content' => "My whole stylesheet is one 400-line file and I like it. Everyone online says Tailwind.\n\nFor a small forum, is there a real reason to switch?",
            ],
            [
                'key' => 'policies-draft',
                'days' => 1,
                'admin' => true,
                'status' => PostStatus::Draft,
                'title' => 'Notes for my talk on policies and gates',
                'category' => 'tutorial',
                'tags' => ['laravel', 'security'],
                'content' => "Gate: may this user do X? Policy: may this user do X to this record?\n\nTODO: add the updateRole example and the denyAsNotFound trick for drafts.",
            ],
            [
                'key' => 'frameworks-draft',
                'days' => 2.5,
                'status' => PostStatus::Draft,
                'title' => 'Half-finished: comparing PHP frameworks',
                'category' => 'other',
                'tags' => ['php'],
                'content' => 'Laravel, Symfony, Slim... Still collecting notes.',
            ],
            [
                'key' => 'ideas-draft',
                'days' => 20,
                'status' => PostStatus::Draft,
                'title' => 'Ideas for my next side project',
                'category' => 'other',
                'tags' => ['career'],
                'content' => "1. A habit tracker\n2. A tiny link shortener\n3. Something with queues",
            ],
            [
                'key' => 'xampp',
                'days' => 26,
                'status' => PostStatus::Archived,
                'title' => 'Old: setting up XAMPP on Windows',
                'category' => 'tutorial',
                'tags' => ['php', 'mysql'],
                'images' => ['XAMPP control panel'],
                'content' => "Out of date now; I switched to installing PHP directly.\n\nKeeping it for reference: start Apache and MySQL from the control panel, then open localhost/phpmyadmin.",
            ],
            [
                'key' => 'announcement',
                'days' => 27,
                'status' => PostStatus::Archived,
                'title' => 'Old announcement: the forum is moving to Laravel',
                'category' => 'news',
                'tags' => ['laravel'],
                'content' => 'The move is done, so this announcement is archived.',
            ],
            [
                'key' => 'ignore',
                'days' => 17,
                'title' => 'Ignore this, testing the editor',
                'category' => 'other',
                'tags' => [],
                'content' => 'test test 123',
            ],
            [
                'key' => 'wrong-account',
                'days' => 18,
                'title' => 'Wrong account, please ignore',
                'category' => 'other',
                'tags' => [],
                'content' => 'Posted from the wrong account by mistake.',
            ],
        ];
    }
}
