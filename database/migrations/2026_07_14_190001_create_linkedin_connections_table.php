<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('linkedin_connections')) {
            return;
        }

        Schema::create('linkedin_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider_user_id')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('scopes')->nullable();
            $table->string('profile_url')->nullable();
            $table->string('profile_name')->nullable();
            $table->string('profile_picture_url')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('raw_profile')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('provider_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_connections');
    }
};
