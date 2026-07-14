<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('instagram_accounts')) {
            return;
        }

        Schema::create('instagram_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('page_id')->nullable()->index();
            $table->string('page_name')->nullable();
            $table->string('business_account_id')->nullable()->unique();
            $table->string('username')->nullable()->index();
            $table->string('name')->nullable();
            $table->text('biography')->nullable();
            $table->string('website')->nullable();
            $table->string('profile_picture_url')->nullable();
            $table->text('access_token');
            $table->timestamp('token_expires_at')->nullable();
            $table->unsignedInteger('followers_count')->default(0);
            $table->unsignedInteger('follows_count')->default(0);
            $table->unsignedInteger('media_count')->default(0);
            $table->json('latest_account_insights')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_synced_status')->default('pending');
            $table->text('last_synced_error')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instagram_accounts');
    }
};
