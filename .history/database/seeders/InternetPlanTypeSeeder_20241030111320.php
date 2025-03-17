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
            'Days',
            'Months',
            'Years'
        ];

        foreach ($types as $
        DB::table('internet_plan_types')->insert([
            'type_name' => $type,
        ]);
    }
}
