<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tr069_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tr069_device_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->string('status')->default('pending');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr069_logs');
    }
};
