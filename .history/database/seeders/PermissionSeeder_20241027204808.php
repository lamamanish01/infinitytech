<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->create([
            'name' => 'view users',
            'name' => 'edit Users',
            'name' => 'create Users',
            'name' => 'create Users',
        ]);
    }
}
