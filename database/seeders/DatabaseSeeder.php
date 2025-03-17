<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\PermissionSeeder;

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

        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'lamamanish234@gmail.com',
            'password' => Hash::make('M@nish#1234')

            // 'name' => 'Rejina Lama',
            // 'email' => 'tamangrejina237@gmail.com',
            // 'password' => Hash::make('rejina@1234')
        ]);

        $role = $user->assignRole('Super Admin');

        $this->call(InternetPlanTypeSeeder::class);
    }
}
