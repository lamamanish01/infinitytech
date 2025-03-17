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
            'name' => 'create users',
            'name' => 'edit users',
            'name' => 'delete Users',
            'name' => 'view roles',
            'name' => 'create roles',
            'name' => 'edit roles',
            'name' => 'delete roles',
            'name' => 'view permissions',
            'name' => 'create permissions',
            'name' => 'edit permissions',
            'name' => 'dete permissions',
        ]);
    }
}
