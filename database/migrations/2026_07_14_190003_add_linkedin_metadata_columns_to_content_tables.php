<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'linkedin_profile_id')) {
                $table->string('linkedin_profile_id')->nullable()->after('role');
            }

            if (! Schema::hasColumn('users', 'linkedin_url')) {
                $table->string('linkedin_url')->nullable()->after('linkedin_profile_id');
            }

            if (! Schema::hasColumn('users', 'linkedin_headline')) {
                $table->string('linkedin_headline')->nullable()->after('linkedin_url');
            }

            if (! Schema::hasColumn('users', 'linkedin_synced_at')) {
                $table->timestamp('linkedin_synced_at')->nullable()->after('linkedin_headline');
            }
        });

        Schema::table('experiences', function (Blueprint $table) {
            if (! Schema::hasColumn('experiences', 'source')) {
                $table->string('source')->default('manual')->after('active');
            }

            if (! Schema::hasColumn('experiences', 'external_id')) {
                $table->string('external_id')->nullable()->after('source');
            }

            if (! Schema::hasColumn('experiences', 'linkedin_url')) {
                $table->string('linkedin_url')->nullable()->after('external_id');
            }

            if (! Schema::hasColumn('experiences', 'synced_at')) {
                $table->timestamp('synced_at')->nullable()->after('linkedin_url');
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'source')) {
                $table->string('source')->default('manual')->after('active');
            }

            if (! Schema::hasColumn('projects', 'external_id')) {
                $table->string('external_id')->nullable()->after('source');
            }

            if (! Schema::hasColumn('projects', 'linkedin_url')) {
                $table->string('linkedin_url')->nullable()->after('external_id');
            }

            if (! Schema::hasColumn('projects', 'synced_at')) {
                $table->timestamp('synced_at')->nullable()->after('linkedin_url');
            }
        });

        Schema::table('blocks', function (Blueprint $table) {
            if (! Schema::hasColumn('blocks', 'source')) {
                $table->string('source')->default('manual')->after('content');
            }

            if (! Schema::hasColumn('blocks', 'external_id')) {
                $table->string('external_id')->nullable()->after('source');
            }

            if (! Schema::hasColumn('blocks', 'linkedin_url')) {
                $table->string('linkedin_url')->nullable()->after('external_id');
            }

            if (! Schema::hasColumn('blocks', 'synced_at')) {
                $table->timestamp('synced_at')->nullable()->after('linkedin_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'linkedin_synced_at',
                'linkedin_headline',
                'linkedin_url',
                'linkedin_profile_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('experiences', function (Blueprint $table) {
            $columns = ['synced_at', 'linkedin_url', 'external_id', 'source'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('experiences', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            $columns = ['synced_at', 'linkedin_url', 'external_id', 'source'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('projects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('blocks', function (Blueprint $table) {
            $columns = ['synced_at', 'linkedin_url', 'external_id', 'source'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('blocks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
