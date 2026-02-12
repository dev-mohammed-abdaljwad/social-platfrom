<?php

namespace Database\Seeders;

use App\Enums\PrivacyTypeEnum;
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
        
        // Create 1000 public posts
        $this->command->info('Creating 1000 public posts...');
        
        $userIds = $users->pluck('id')->toArray();
        
        for ($i = 0; $i < 1000; $i++) {
            Post::factory()->create([
                'user_id' => $userIds[array_rand($userIds)],
                'privacy' => PrivacyTypeEnum::Public->value,
            ]);
            
            if (($i + 1) % 100 === 0) {
                $this->command->info("Created " . ($i + 1) . " posts...");
            }
        }
        
        $this->command->info('Done! Total public posts: ' . Post::where('privacy', 'public')->count());
    }
}
