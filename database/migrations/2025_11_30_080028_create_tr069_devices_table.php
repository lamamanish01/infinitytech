<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tr069_devices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tr069_server_id')
                ->nullable()
                ->constrained('tr069_servers')
                ->nullOnDelete();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->string('serial_number')->unique();

            // 👇 ADDED: PPPoE / WAN username
            $table->string('username')->nullable();

            $table->string('oui')->nullable();
            $table->string('product_class')->nullable();

            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();

            $table->string('mac_address')->nullable();
            $table->ipAddress('ip_address')->nullable();

            $table->timestamp('last_inform')->nullable();

            $table->string('status')->default('offline'); // online | offline

            $table->timestamps();

            $table->index(['serial_number']);
            $table->index(['status']);
            $table->index(['username']); // 👈 useful for ISP search
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr069_devices');
    }
};
