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
        Schema::table('project_and_camera', function (Blueprint $table) {
            Schema::table('projects', function (Blueprint $table) {
                $table->boolean('active')->default(false);
            });

            Schema::table('cameras', function (Blueprint $table) {
                $table->boolean('active')->default(false);
            });

            Schema::table('photographies', function (Blueprint $table) {
                $table->boolean('active')->default(true);
            });

            Schema::table('experiences', function (Blueprint $table) {
                $table->boolean('active')->default(false);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_and_camera', function (Blueprint $table) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('active');
            });

            Schema::table('cameras', function (Blueprint $table) {
                $table->dropColumn('active');
            });

            Schema::table('photographies', function (Blueprint $table) {
                $table->dropColumn('active');
            });

            Schema::table('experiences', function (Blueprint $table) {
                $table->dropColumn('active');
            });
        });
    }
};
