<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\CronJobSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(BranchSeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(CronJobSeeder::class);

        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'lamamanish234@gmail.com',
            'password' => Hash::make('M@nish#1234')
        ]);

        $role = $user->assignRole('Super Admin');

        $this->call(InternetPlanTypeSeeder::class);
    }
}
