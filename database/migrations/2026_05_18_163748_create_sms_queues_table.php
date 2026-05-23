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
        Schema::create('sms_queues', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('mobile');
            $table->text('message');
            $table->string('type');
            $table->string('status')->default('pending');
            $table->integer('retry_count')->default(0);
            $table->timestamp('send_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_queues');
    }
};
