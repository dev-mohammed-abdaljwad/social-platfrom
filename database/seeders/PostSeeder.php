<?php

namespace Database\Seeders;

use App\Enums\PrivacyTypeEnum;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users or create some
        $users = User::all();
        
        if ($users->isEmpty()) {
            $users = User::factory(5)->create();
        }
        
        // Create 1000 public posts with comments and likes
        $this->command->info('Creating 1000 public posts with comments and reactions...');
        
        $userIds = $users->pluck('id')->toArray();
        
        for ($i = 0; $i < 1000; $i++) {
            $post = Post::factory()->create([
                'user_id' => $userIds[array_rand($userIds)],
                'privacy' => PrivacyTypeEnum::Public->value,
            ]);
            
            // Add 10 comments for each post
            for ($j = 0; $j < 10; $j++) {
                Comment::factory()->create([
                    'user_id' => $userIds[array_rand($userIds)],
                    'post_id' => $post->id,
                ]);
            }
            
            // Add random number of reactions (1-20) for each post
            $likesCount = rand(1, 20);
            $likedByUsers = array_rand(array_flip($userIds), min($likesCount, count($userIds)));
            
            if (!is_array($likedByUsers)) {
                $likedByUsers = [$likedByUsers];
            }
            
            foreach ($likedByUsers as $userId) {
                \App\Models\Reaction::factory()->create([
                    'user_id' => $userId,
                    'reactable_id' => $post->id,
                    'reactable_type' => Post::class,
                ]);
            }
            
            if (($i + 1) % 100 === 0) {
                $this->command->info("Created " . ($i + 1) . " posts with comments and reactions...");
            }
        }
        
        $this->command->info('Done!');
        $this->command->info('Total public posts: ' . Post::where('privacy', 'public')->count());
        $this->command->info('Total comments: ' . Comment::count());
        $this->command->info('Total reactions: ' . \App\Models\Reaction::count());
    }
}
