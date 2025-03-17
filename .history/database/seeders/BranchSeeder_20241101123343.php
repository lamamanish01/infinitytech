<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::create([
            'name' => 'Head Office',
            'address' => 'Nayapati'
            'contact_number' => '9860741321',
            'amount' => '0.00',
        ]);
    }
}
