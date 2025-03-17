<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class InternetPlanTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $type = [
            '1' => 'Days',
            '2' => 'Months',
            '2' => 'Years'
        ];

        DB::table('internet_plan_types')->insert($type);
    }
}
