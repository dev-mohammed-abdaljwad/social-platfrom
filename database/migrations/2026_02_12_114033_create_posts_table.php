<?php

use App\Enums\ContentTypeEnum;
use App\Enums\PrivacyTypeEnum;
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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // fields
            $table->text('content');
            $table->string('image')->nullable();
            $table->string('video')->nullable();
            $table->string('location')->nullable();
            $table->enum('privacy', PrivacyTypeEnum::getValues())->default(PrivacyTypeEnum::Public->value);    
            $table->enum('type', ContentTypeEnum::getValues())->default(ContentTypeEnum::Text->value);
            $table->index(['user_id', 'privacy']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
