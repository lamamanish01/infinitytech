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
        Schema::create('recharges', function (Blueprint $table) {

            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('internet_plan_id')->constrained('internet_plans')->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->dateTime('recharge_date');
            $table->dateTime('expire_date');
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recharges');
    }
};
