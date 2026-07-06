<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tr069_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('acs_url');
            $table->string('acs_username')->nullable();
            $table->string('acs_password')->nullable();
            $table->string('status')->default('active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr069_servers');
    }
};
