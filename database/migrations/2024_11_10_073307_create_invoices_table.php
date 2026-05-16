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
        Schema::create('invoices', function (Blueprint $table) {

            $table->id();
            $table->foreignId('billing_id')->constrained('billings')->cascadeOnDelete();
            $table->string('invoice_no')->nullable()->unique();
            $table->date('invoice_date');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
