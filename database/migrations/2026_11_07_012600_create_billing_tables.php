<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_settings', function (Blueprint $table) {
            $table->id();
            $table->string('issuer_name')->nullable();
            $table->string('issuer_phone')->nullable();
            $table->string('issuer_email')->nullable();
            $table->string('issuer_website')->nullable();
            $table->text('issuer_address')->nullable();
            $table->string('payment_order')->nullable();
            $table->string('account_number')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('thank_you')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('number')->unique();
            $table->date('issued_on');
            $table->string('status')->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->text('client_address')->nullable();
            $table->decimal('vat_rate', 5, 2)->default(20);
            $table->string('issuer_name')->nullable();
            $table->string('issuer_phone')->nullable();
            $table->string('issuer_email')->nullable();
            $table->string('issuer_website')->nullable();
            $table->text('issuer_address')->nullable();
            $table->string('payment_order')->nullable();
            $table->string('account_number')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('thank_you')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('quantity', 8, 2);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('billing_settings');
    }
};
