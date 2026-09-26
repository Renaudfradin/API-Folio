<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('linkedin_connections', function (Blueprint $table): void {
            if (! Schema::hasColumn('linkedin_connections', 'followers_count')) {
                $table->unsignedInteger('followers_count')->default(0)->after('profile_picture_url');
            }

            if (! Schema::hasColumn('linkedin_connections', 'connection_type')) {
                $table->string('connection_type')->default('member')->after('followers_count');
            }

            if (! Schema::hasColumn('linkedin_connections', 'organization_urn')) {
                $table->string('organization_urn')->nullable()->after('connection_type');
            }

            if (! Schema::hasColumn('linkedin_connections', 'organization_name')) {
                $table->string('organization_name')->nullable()->after('organization_urn');
            }

            if (! Schema::hasColumn('linkedin_connections', 'last_synced_status')) {
                $table->string('last_synced_status')->nullable()->after('last_synced_at');
            }

            if (! Schema::hasColumn('linkedin_connections', 'last_synced_error')) {
                $table->text('last_synced_error')->nullable()->after('last_synced_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('linkedin_connections', function (Blueprint $table): void {
            $columns = [
                'last_synced_error',
                'last_synced_status',
                'organization_name',
                'organization_urn',
                'connection_type',
                'followers_count',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('linkedin_connections', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
