<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNasreloadTable extends Migration
{
    public function up()
    {
        Schema::create('nasreload', function (Blueprint $table) {
            $table->string('nasipaddress', 15)->primary();
            $table->dateTime('reloadtime');
        });
    }

    public function down()
    {
        Schema::dropIfExists('nasreload');
    }
}


