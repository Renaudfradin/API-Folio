<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('instagram_sync_runs')) {
            return;
        }

        Schema::create('instagram_sync_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instagram_account_id')->nullable()->constrained('instagram_accounts')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('records_synced')->default(0);
            $table->text('error_message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instagram_sync_runs');
    }
};
