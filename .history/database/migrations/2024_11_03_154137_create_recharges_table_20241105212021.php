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
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('internetplan_id')->references('id')->on('internetplans');
            $table->date('recharge_date');
            $table->date('expiry_date');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::table('customers', function )
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recharges');
    }
};
