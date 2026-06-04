<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cron_jobs', function (Blueprint $table) {

            if (!Schema::hasColumn('cron_jobs', 'last_run_at')) {
                $table->timestamp('last_run_at')
                    ->nullable()
                    ->after('frequency');
            }

        });
    }

    public function down(): void
    {
        Schema::table('cron_jobs', function (Blueprint $table) {

            if (Schema::hasColumn('cron_jobs', 'last_run_at')) {
                $table->dropColumn('last_run_at');
            }

        });
    }
};
