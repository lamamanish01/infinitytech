<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRadpostauthTable extends Migration
{
    public function up()
    {
        Schema::create('radpostauth', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 64)->default('');
            $table->string('pass', 64)->default('');
            $table->string('reply', 32)->default('');
            $table->string('reply_message', 32)->default('');
            $table->timestamp('authdate', 6)->useCurrent()->useCurrentOnUpdate();
            $table->string('nasipaddress', 15)->default('');
            $table->string('mac', 48)->default('');
        });
    }

    public function down()
    {
        Schema::dropIfExists('radpostauth');
    }
}

