<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('instagram_media')) {
            return;
        }

        Schema::create('instagram_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instagram_account_id')->constrained('instagram_accounts')->cascadeOnDelete();
            $table->string('media_id')->unique();
            $table->text('caption')->nullable();
            $table->string('permalink')->nullable();
            $table->string('media_type');
            $table->string('media_product_type')->nullable();
            $table->string('media_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->unsignedInteger('like_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamp('timestamp')->nullable();
            $table->json('insights')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instagram_media');
    }
};
