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
        Schema::create('tr069_servers', function (Blueprint $table) {
            $table->id();
            table->string('name')->nullable();
            $table->string('ip')->nullable();
            $table->integer('web_port')->default(3000);
            $table->integer('api_port')->default(7557);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tr069_servers');
    }
};
