<?php

namespace Tests\Feature;

use App\Enums\FriendshipStatusEnum;
use App\Enums\PrivacyTypeEnum;
use App\Enums\ReactionTypeEnum;
use App\Models\Comment;
use App\Models\Friendship;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Share;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class FeedFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear Redis before each test to ensure a clean state for Smart Feed
        Redis::flushall();
    }

    /** @test */
    public function users_can_see_their_own_posts_in_the_feed()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'content' => 'My own post',
            'privacy' => PrivacyTypeEnum::Public->value
        ]);

        $this->actingAs($user);

        $response = $this->get('/');

        $response->setStatus(200);
        $response->assertSee('My own post');
    }

    /** @test */
    public function users_can_see_friends_posts_in_the_feed()
    {
        $user = User::factory()->create();
        $friend = User::factory()->create();

        // Create accepted friendship
        Friendship::create([
            'sender_id' => $user->id,
            'receiver_id' => $friend->id,
            'status' => FriendshipStatusEnum::Accepted->value
        ]);

        $post = Post::factory()->create([
            'user_id' => $friend->id,
            'content' => 'Friends post',
            'privacy' => PrivacyTypeEnum::Friends->value
        ]);

        $this->actingAs($user);

        $response = $this->get('/');

        $response->setStatus(200);
        $response->assertSee('Friends post');
    }

    /** @test */
    public function users_cannot_see_non_friends_private_posts()
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();

        $post = Post::factory()->create([
            'user_id' => $stranger->id,
            'content' => 'Secret post',
            'privacy' => PrivacyTypeEnum::Friends->value
        ]);

        $this->actingAs($user);

        $response = $this->get('/');

        $response->setStatus(200);
        $response->assertDontSee('Secret post');
    }

    /** @test */
    public function smart_feed_ranks_posts_with_higher_engagement_first()
    {
        $user = User::factory()->create();
        $friend = User::factory()->create();

        Friendship::create([
            'sender_id' => $user->id,
            'receiver_id' => $friend->id,
            'status' => FriendshipStatusEnum::Accepted->value
        ]);

        // Post A: Older but with lots of engagement
        $postA = Post::factory()->create([
            'user_id' => $friend->id,
            'content' => 'High Engagement Post',
            'created_at' => now()->subHours(2)
        ]);

        // Add engagement to Post A
        for ($i = 0; $i < 10; $i++) {
            Reaction::create([
                'user_id' => User::factory()->create()->id,
                'reactable_id' => $postA->id,
                'reactable_type' => Post::class,
                'type' => ReactionTypeEnum::LIKE->value
            ]);
        }

        // Post B: Newer but no engagement
        $postB = Post::factory()->create([
            'user_id' => $friend->id,
            'content' => 'New Low Engagement Post',
            'created_at' => now()->subMinute()
        ]);

        $this->actingAs($user);

        // First hit triggers Smart Feed generation
        $response = $this->get('/');

        $response->setStatus(200);

        // In the Smart Feed, High Engagement Post should appear first 
        // even if slightly older than a brand new post with 0 likes.
        $content = $response->getContent();
        $posA = strpos($content, 'High Engagement Post');
        $posB = strpos($content, 'New Low Engagement Post');

        $this->assertTrue($posA < $posB, "Engaged post should appear before unengaged newer post");
    }

    /** @test */
    public function engagement_counters_increment_accurately()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertEquals(0, $post->fresh()->likes_count);

        // Add like
        Reaction::create([
            'user_id' => $user->id,
            'reactable_id' => $post->id,
            'reactable_type' => Post::class,
            'type' => ReactionTypeEnum::LIKE->value
        ]);

        $this->assertEquals(1, $post->fresh()->likes_count);

        // Add comment
        Comment::create([
            'user_id' => User::factory()->create()->id,
            'post_id' => $post->id,
            'content' => 'Test comment'
        ]);

        $this->assertEquals(1, $post->fresh()->comments_count);

        // Add share
        Share::create([
            'user_id' => User::factory()->create()->id,
            'post_id' => $post->id,
            'content' => 'Sharing this'
        ]);

        $this->assertEquals(1, $post->fresh()->shares_count);
    }
}
