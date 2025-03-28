<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRadacctTable extends Migration
{
    public function up()
    {
        Schema::create('radacct', function (Blueprint $table) {
            $table->bigIncrements('radacctid');
            $table->string('acctsessionid', 64)->default('');
            $table->string('acctuniqueid', 32)->unique();
            $table->string('username', 64)->default('');
            $table->string('groupname', 64)->default('');
            $table->string('realm', 64)->nullable();
            $table->string('nasipaddress', 15)->default('');
            $table->string('nasportid', 32)->nullable();
            $table->string('nasporttype', 32)->nullable();
            $table->dateTime('acctstarttime')->nullable();
            $table->dateTime('acctupdatetime')->nullable();
            $table->dateTime('acctstoptime')->nullable();
            $table->integer('acctinterval')->nullable();
            $table->unsignedInteger('acctsessiontime')->nullable();
            $table->string('acctauthentic', 32)->nullable();
            $table->string('connectinfo_start', 50)->nullable();
            $table->string('connectinfo_stop', 50)->nullable();
            $table->bigInteger('acctinputoctets')->nullable();
            $table->bigInteger('acctoutputoctets')->nullable();
            $table->string('calledstationid', 50)->default('');
            $table->string('callingstationid', 50)->default('');
            $table->string('acctterminatecause', 32)->default('');
            $table->string('servicetype', 32)->nullable();
            $table->string('framedprotocol', 32)->nullable();
            $table->string('framedipaddress', 15)->default('');
            $table->string('framedipv6address', 45)->default('');
            $table->string('framedipv6prefix', 45)->default('');
            $table->string('framedinterfaceid', 44)->default('');
            $table->string('delegatedipv6prefix', 45)->default('');
            $table->string('class', 64)->nullable();
            $table->index(['acctstoptime', 'nasipaddress', 'acctstarttime'], 'bulk_close');
        });
    }

    public function down()
    {
        Schema::dropIfExists('radacct');
    }
}

