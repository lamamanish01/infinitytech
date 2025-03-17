<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permission = new Permission();
        $permissions = [
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
            'name' => 'delete permissions',
        ];

        foreach ($users as $
    }
}
