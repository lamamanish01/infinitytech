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
            $table->foreignId('tr069_server_id')->constrained('tr069_servers')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

            $table->string('_id')->nullable();
            $table->string('encoded_id')->nullable();
            $table->string('serial')->unique()->index();
            $table->string('oui')->nullable();
            $table->string('product_class')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();

            $table->string('ppp_username')->nullable();
            $table->string('ppp_password')->nullable();

            $table->string('onu_mac')->nullable()->index();
            $table->string('router_mac')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('ip_address')->nullable();

            $table->string('wifi_24_ssid')->nullable();
            $table->string('wifi_24_password')->nullable();
            $table->boolean('wifi_24_hidden')->default(false);

            $table->string('wifi_5_ssid')->nullable();
            $table->string('wifi_5_password')->nullable();
            $table->boolean('wifi_5_hidden')->default(false);

            $table->string('user_username')->nullable();
            $table->string('user_password')->nullable();
            $table->string('admin_username')->nullable();
            $table->string('admin_password')->nullable();

            $table->string('status')->default('offline')->index();
            $table->timestamp('last_inform')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr069_devices');
    }
};
