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
            '0' => 'Days',
            '1' => 'Months',
            'type_name' => 'Years'
        ];

        DB::table('internet_plan_types')->insert($type);
    }
}
