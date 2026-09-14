<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instagram_accounts', function (Blueprint $table): void {
            $table->text('website')->nullable()->change();
            $table->text('profile_picture_url')->nullable()->change();
        });

        if (Schema::hasTable('instagram_media')) {
            Schema::table('instagram_media', function (Blueprint $table): void {
                $table->text('permalink')->nullable()->change();
                $table->text('media_url')->nullable()->change();
                $table->text('thumbnail_url')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('instagram_accounts', function (Blueprint $table): void {
            $table->string('website')->nullable()->change();
            $table->string('profile_picture_url')->nullable()->change();
        });

        if (Schema::hasTable('instagram_media')) {
            Schema::table('instagram_media', function (Blueprint $table): void {
                $table->string('permalink')->nullable()->change();
                $table->string('media_url')->nullable()->change();
                $table->string('thumbnail_url')->nullable()->change();
            });
        }
    }
};
