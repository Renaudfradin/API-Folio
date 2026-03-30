<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        DB::table('employments')
            ->where('responce', 'false')
            ->orWhere('responce', 0)
            ->update(['responce' => 'no']);

        DB::table('employments')
            ->where('responce', 'true')
            ->orWhere('responce', 1)
            ->update(['responce' => 'yes']);

        DB::table('employments')
            ->whereNull('responce')
            ->update(['responce' => 'pending']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        DB::table('employments')
            ->where('responce', 'no')
            ->orWhere('responce', 0)
            ->update(['responce' => 0]);

        DB::table('employments')
            ->where('responce', 'yes')
            ->orWhere('responce', 1)
            ->update(['responce' => 1]);

        DB::table('employments')
            ->where('responce', 'pending')
            ->update(['responce' => null]);
    }
};
