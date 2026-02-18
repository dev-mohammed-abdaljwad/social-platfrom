<?php

namespace Database\Seeders;

use App\Enums\ContentTypeEnum;
use App\Enums\PrivacyTypeEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MassSeeder extends Seeder
{
    protected static ?string $password = null;

    /**
     * Seed the application's database with mass data.
     * 10K users, 100K posts, 500K comments
     */
    public function run(): void
    {
        // Disable query log to save memory
        DB::disableQueryLog();
        
        // Increase memory limit for this operation
        ini_set('memory_limit', '512M');

        $this->command->info('Starting mass seeding...');
        $this->command->info('This may take several minutes.');

        $startTime = microtime(true);

        // Create users
        $userCount = $this->createUsers(10000);
        
        // Create posts
        $postCount = $this->createPosts(100000, $userCount);
        
        // Create comments
        $this->createComments(500000, $userCount, $postCount);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->command->newLine();
        $this->command->info("Mass seeding completed in {$duration} seconds!");
    }

    protected function createUsers(int $count): int
    {
        $this->command->info("Creating {$count} users...");

        static::$password ??= Hash::make('password');
        $chunkSize = 500;
        $bar = $this->command->getOutput()->createProgressBar($count);

        // Create test user first
        DB::table('users')->insert([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => static::$password,
            'remember_token' => Str::random(10),
            'bio' => 'Test user account',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $bar->advance();

        // Create remaining users in chunks
        $remaining = $count - 1;
        $privacyValues = PrivacyTypeEnum::getValues();
        $cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose'];
        $bios = ['Love coding!', 'Coffee addict', 'Travel enthusiast', 'Music lover', 'Foodie', 'Gamer', 'Sports fan', 'Nature lover', null, null];

        for ($i = 0; $i < $remaining; $i += $chunkSize) {
            $chunk = min($chunkSize, $remaining - $i);
            $users = [];
            $baseIndex = $i + 2; // Start from 2 (after test user)

            for ($j = 0; $j < $chunk; $j++) {
                $idx = $baseIndex + $j;
                $users[] = [
                    'name' => 'User ' . $idx,
                    'username' => 'user' . $idx,
                    'email' => 'user' . $idx . '@example.com',
                    'email_verified_at' => now(),
                    'password' => static::$password,
                    'remember_token' => Str::random(10),
                    'bio' => $bios[array_rand($bios)],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('users')->insert($users);
            $bar->advance($chunk);
            
            unset($users);
        }

        $bar->finish();
        $this->command->newLine();

        return $count;
    }

    protected function createPosts(int $count, int $userCount): int
    {
        $this->command->info("Creating {$count} posts...");

        $chunkSize = 500;
        $bar = $this->command->getOutput()->createProgressBar($count);
        $privacyValues = PrivacyTypeEnum::getValues();
        
        $contents = [
            'Just had an amazing day! #blessed',
            'Working on something exciting...',
            'Beautiful sunset today 🌅',
            'Coffee is life ☕',
            'Weekend vibes! 🎉',
            'New project coming soon!',
            'Learning something new every day.',
            'Great meeting with the team today!',
            'Enjoying the little things in life.',
            'Motivation Monday! Let\'s go!',
        ];

        for ($i = 0; $i < $count; $i += $chunkSize) {
            $chunk = min($chunkSize, $count - $i);
            $posts = [];

            for ($j = 0; $j < $chunk; $j++) {
                $daysAgo = rand(0, 365);
                $createdAt  = now()
    ->subDays($daysAgo)
    ->subHours(rand(0, 23))
    ->subMinutes(rand(0, 59))
    ->format('Y-m-d H:i:s');
                
                $posts[] = [
                    'user_id' => rand(1, $userCount),
                    'content' => $contents[array_rand($contents)] . ' ' . Str::random(20),
                    'image' => null,
                    'video' => null,
                    'location' => rand(0, 10) < 3 ? ['NYC', 'LA', 'Chicago', 'Miami', 'Seattle'][rand(0, 4)] : null,
                    'privacy' => $privacyValues[array_rand($privacyValues)],
                    'type' => ContentTypeEnum::Text->value,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }

            DB::table('posts')->insert($posts);
            $bar->advance($chunk);
            
            unset($posts);
            
            // Garbage collection every 10k
            if ($i % 10000 === 0 && $i > 0) {
                gc_collect_cycles();
            }
        }

        $bar->finish();
        $this->command->newLine();

        return $count;
    }

    protected function createComments(int $count, int $userCount, int $postCount): void
    {
        $this->command->info("Creating {$count} comments...");

        $chunkSize = 1000;
        $bar = $this->command->getOutput()->createProgressBar($count);
        
        $contents = [
            'Great post!',
            'Love this! 👍',
            'So true!',
            'Awesome!',
            'Thanks for sharing!',
            'This is amazing!',
            'Couldn\'t agree more.',
            'Nice one!',
            'Keep it up!',
            '❤️',
        ];

        for ($i = 0; $i < $count; $i += $chunkSize) {
            $chunk = min($chunkSize, $count - $i);
            $comments = [];

            for ($j = 0; $j < $chunk; $j++) {
                $daysAgo = rand(0, 365);
                $createdAt  = now()
    ->subDays($daysAgo)
    ->subHours(rand(0, 23))
    ->subMinutes(rand(0, 59))
    ->format('Y-m-d H:i:s');
                
                $comments[] = [
                    'user_id' => rand(1, $userCount),
                    'post_id' => rand(1, $postCount),
                    'parent_id' => null,
                    'content' => $contents[array_rand($contents)] . ' ' . Str::random(10),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }

            DB::table('comments')->insert($comments);
            $bar->advance($chunk);
            
            unset($comments);
            
            // Garbage collection every 50k
            if ($i % 50000 === 0 && $i > 0) {
                gc_collect_cycles();
            }
        }

        $bar->finish();
        $this->command->newLine();
    }
}
