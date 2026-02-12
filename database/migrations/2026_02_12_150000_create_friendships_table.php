<?php

use App\Enums\FriendshipStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', FriendshipStatusEnum::getValues())->default(FriendshipStatusEnum::Pending->value);
            $table->timestamps();

            // Ensure unique friendship pair (either direction)
            $table->unique(['sender_id', 'receiver_id']);
            $table->index(['sender_id', 'status']);
            $table->index(['receiver_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};
