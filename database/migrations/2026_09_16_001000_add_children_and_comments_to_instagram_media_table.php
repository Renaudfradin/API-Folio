<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('instagram_media')) {
            return;
        }

        Schema::table('instagram_media', function (Blueprint $table): void {
            if (! Schema::hasColumn('instagram_media', 'children')) {
                $table->json('children')->nullable()->after('raw_data');
            }
            if (! Schema::hasColumn('instagram_media', 'comments')) {
                $table->json('comments')->nullable()->after('children');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('instagram_media')) {
            return;
        }

        Schema::table('instagram_media', function (Blueprint $table): void {
            if (Schema::hasColumn('instagram_media', 'comments')) {
                $table->dropColumn('comments');
            }
            if (Schema::hasColumn('instagram_media', 'children')) {
                $table->dropColumn('children');
            }
        });
    }
};
