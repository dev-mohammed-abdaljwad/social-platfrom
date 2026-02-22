<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillEngagement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-engagement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill post engagement counters';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Backfilling likes count...');
        DB::statement("UPDATE posts p SET likes_count = (SELECT count(*) FROM reactions r WHERE r.reactable_type = 'App\\\\Models\\\\Post' AND r.reactable_id = p.id)");

        $this->info('Backfilling comments count...');
        DB::statement('UPDATE posts p SET comments_count = (SELECT count(*) FROM comments c WHERE c.post_id = p.id)');

        $this->info('Backfilling shares count...');
        DB::statement('UPDATE posts p SET shares_count = (SELECT count(*) FROM shares s WHERE s.post_id = p.id)');

        $this->info('Done!');
    }
}
