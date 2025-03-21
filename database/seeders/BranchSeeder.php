<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::create([
            'name' => 'Head Office',
            'address' => 'Nayapati 02',
            'contact_number' => '9860741321',
            'balance' => '0.00',
        ]);
    }
}
