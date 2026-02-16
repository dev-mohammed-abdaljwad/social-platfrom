<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ReactionTypeEnum;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // i will make reaction types enums like 'like', 'love', 'haha', 'wow', 'sad', 'angry'  
    public function up(): void
    {
        Schema::table('likes', function (Blueprint $table) {
            $table->enum('reaction_type', ReactionTypeEnum::getValues())->default(ReactionTypeEnum::LIKE->value)->after('likeable_type'); // Add reaction_type column with default value 'like'     
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('likes', function (Blueprint $table) {
            $table->dropColumn('reaction_type');
        });
    }
};
