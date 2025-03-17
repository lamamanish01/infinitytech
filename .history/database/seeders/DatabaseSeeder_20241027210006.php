<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Manish Lama',
            'email' => 'lamamanish234@gmail.com',
            'role' => 'Super Admin',
            'password' => Hash::make('M@nish#1234')

            'name' => 'Manish Lama',
            'email' => 'lamamanish234@gmail.com',
            'role' => 'Super Admin',
            'password' => Hash::make('M@nish#1234')
        ]);

        $user->syncRole('Super Admin');

        $this->call(RoleSeeder::class);
    }
}
