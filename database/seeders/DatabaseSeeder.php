<?php

namespace Database\Seeders;

use App\Enums\FriendshipStatusEnum;
use App\Models\Comment;
use App\Models\Friendship;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user with known credentials
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create 19 more users (total 20)
        $users = User::factory(19)->create();
        $allUsers = $users->prepend($testUser);

        $this->command->info('Created 20 users');

        // Create posts for each user (2-5 posts per user)
        $posts = collect();
        foreach ($allUsers as $user) {
            $userPosts = Post::factory(rand(2, 5))->create([
                'user_id' => $user->id,
            ]);
            $posts = $posts->merge($userPosts);
        }

        $this->command->info('Created ' . $posts->count() . ' posts');

        // Create comments on posts (2-8 comments per post, from random users)
        $comments = collect();
        foreach ($posts as $post) {
            $commentCount = rand(2, 8);
            for ($i = 0; $i < $commentCount; $i++) {
                $comment = Comment::factory()->create([
                    'user_id' => $allUsers->random()->id,
                    'post_id' => $post->id,
                ]);
                $comments->push($comment);

                // 30% chance to add a reply to this comment
                if (rand(1, 100) <= 30) {
                    $reply = Comment::factory()->create([
                        'user_id' => $allUsers->random()->id,
                        'post_id' => $post->id,
                        'parent_id' => $comment->id,
                    ]);
                    $comments->push($reply);
                }
            }
        }

        $this->command->info('Created ' . $comments->count() . ' comments');

        // Create likes on posts and comments
        $likeCount = 0;
        foreach ($posts as $post) {
            // Each post gets likes from 3-10 random users
            $likers = $allUsers->random(rand(3, min(10, $allUsers->count())));
            foreach ($likers as $liker) {
                Like::firstOrCreate([
                    'user_id' => $liker->id,
                    'likeable_id' => $post->id,
                    'likeable_type' => Post::class,
                ]);
                $likeCount++;
            }
        }

        // Add some likes to comments too
        foreach ($comments->random(min(50, $comments->count())) as $comment) {
            $likers = $allUsers->random(rand(1, 5));
            foreach ($likers as $liker) {
                Like::firstOrCreate([
                    'user_id' => $liker->id,
                    'likeable_id' => $comment->id,
                    'likeable_type' => Comment::class,
                ]);
                $likeCount++;
            }
        }

        $this->command->info('Created ' . $likeCount . ' likes');

        // Create friendships between users
        $friendshipCount = 0;
        foreach ($allUsers as $user) {
            // Each user sends friend requests to 3-8 random other users
            $potentialFriends = $allUsers->where('id', '!=', $user->id)->random(rand(3, 8));
            
            foreach ($potentialFriends as $friend) {
                // Check if friendship already exists (in either direction)
                $exists = Friendship::where(function ($q) use ($user, $friend) {
                    $q->where('sender_id', $user->id)->where('receiver_id', $friend->id);
                })->orWhere(function ($q) use ($user, $friend) {
                    $q->where('sender_id', $friend->id)->where('receiver_id', $user->id);
                })->exists();

                if (!$exists) {
                    // 70% accepted, 30% pending
                    $status = rand(1, 100) <= 70 
                        ? FriendshipStatusEnum::Accepted->value 
                        : FriendshipStatusEnum::Pending->value;

                    Friendship::create([
                        'sender_id' => $user->id,
                        'receiver_id' => $friend->id,
                        'status' => $status,
                    ]);
                    $friendshipCount++;
                }
            }
        }

        $this->command->info('Created ' . $friendshipCount . ' friendships');
        $this->command->info('');
        $this->command->info('Seeding complete!');
        $this->command->info('Login with: test@example.com / password');
    }
}
