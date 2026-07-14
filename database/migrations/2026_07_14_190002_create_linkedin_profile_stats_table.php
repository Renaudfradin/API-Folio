<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('linkedin_profile_stats')) {
            return;
        }

        Schema::create('linkedin_profile_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('metric_key');
            $table->string('metric_label');
            $table->decimal('value', 12, 2)->nullable();
            $table->string('value_text')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->string('source')->default('manual');
            $table->json('payload')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'metric_key']);
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_profile_stats');
    }
};
