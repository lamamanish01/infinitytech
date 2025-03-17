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
            $table->string('address');
            $table->bigInteger('primary_contact')->nullable();
            $table->bigInteger('secondary_contact')->nullable();
            $table->dateTime('registered')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->dateTime('grace_period')->nullable();
            $table->dateTime('expired')->nullable();
            $table->string('internetplan')->nullable();
            $table->string('branch')->nullable();
            $table->enum('status', ['active', 'suspended', 'terminated']);
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
