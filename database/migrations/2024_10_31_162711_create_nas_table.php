<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNasTable extends Migration
{
    public function up()
    {
        Schema::create('nas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nasname', 128);
            $table->string('shortname', 32)->nullable();
            $table->string('type', 30)->default('other');
            $table->integer('ports')->nullable();
            $table->string('secret', 60)->default('secret');
            $table->string('server', 64)->nullable();
            $table->string('community', 50)->nullable();
            $table->string('description', 200)->nullable()->default('RADIUS Client');
            $table->index('nasname');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('nas');
    }
}


