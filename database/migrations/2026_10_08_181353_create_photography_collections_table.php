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
        if (Schema::hasTable('photography_collections')) {
            return;
        }

        Schema::create('photography_collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();
        });

        Schema::create('photography_collection_photography', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photography_collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('photography_id')->constrained('photographies')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['photography_collection_id', 'photography_id']);
            $table->index(['photography_collection_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photography_collection_photography');
        Schema::dropIfExists('photography_collections');
    }
};
