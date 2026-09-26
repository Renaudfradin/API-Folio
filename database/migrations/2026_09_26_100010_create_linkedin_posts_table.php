<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('linkedin_posts')) {
            return;
        }

        Schema::create('linkedin_posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('linkedin_connection_id')->constrained('linkedin_connections')->cascadeOnDelete();
            $table->string('post_urn')->unique();
            $table->text('text')->nullable();
            $table->string('permalink')->nullable();
            $table->string('media_type')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('reactions')->default(0);
            $table->unsignedInteger('comments')->default(0);
            $table->unsignedInteger('shares')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('reach')->default(0);
            $table->unsignedInteger('video_views')->default(0);
            $table->decimal('engagement_rate', 8, 2)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['linkedin_connection_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_posts');
    }
};
