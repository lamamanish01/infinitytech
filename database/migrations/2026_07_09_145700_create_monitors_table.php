<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host')->unique();
            $table->enum('check_type', ['ping', 'snmp'])->default('ping');

            // SNMP fields (optional)
            $table->string('snmp_community')->default('public');
            $table->enum('snmp_version', ['v1', 'v2c', 'v3'])->default('v2c');
            $table->unsignedInteger('snmp_port')->default(161);
            $table->unsignedInteger('snmp_timeout')->default(1);
            $table->string('snmp_oid')->default('.1.3.6.1.2.1.1.1.0');

            // Status and metrics
            $table->enum('status', ['up', 'down', 'pending', 'unknown'])->default('unknown');
            $table->decimal('uptime', 5, 2)->nullable();
            $table->unsignedInteger('response_time')->nullable();
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('total_count')->default(0);

            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('last_checked_at');
            $table->index('host');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitors');
    }
};
