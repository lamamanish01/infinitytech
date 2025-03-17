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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('address');
            $table->bigInteger('contact_number')->nullable();
            $table->dateTime('registered')->nullable();
            $table->string('username');
            $table->string('password');
            $table->dateTime('grace_period')->nullable();
            $table->integer('internetplan_id')->nullable();
            $table->integer('branch_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->enum('status', ['active', 'suspended', 'terminated']);
            $table->string('remarks')->nullable();
            $table->integer('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
