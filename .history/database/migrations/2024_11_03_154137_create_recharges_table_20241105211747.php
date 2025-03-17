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
            $table->foreign('customer_id')->references('id')->on('cust')
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('internetplan_id')->constrained()->onDelete('cascade');
            $table->date('recharge_date');
            $table->date('expiry_date');
            $table->string('status')->default('active');
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
