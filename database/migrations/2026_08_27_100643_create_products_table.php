<?php

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->string('youtube_url')->nullable();
            $table->string('youtube_video_id')->nullable();
            $table->timestamp('youtube_found_at')->nullable();
            $table->boolean('ai_verified')->default(false);
            $table->decimal('ai_accuracy', 5, 2)->nullable();
            $table->text('ai_explanation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
