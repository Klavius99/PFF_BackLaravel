<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Database\Seeders\AuthorizedTrainerEmailSeeder;
use Database\Seeders\FormateurSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer le premier admin par défaut
        User::create([
            'username' => 'admin',
            'email' => 'admin@isepdiamniadio.edu.sn',
            'password' => bcrypt('Admin@123'),
            'role' => 'admin',
            'status' => true,
        ]);

        $this->call([
            AuthorizedTrainerEmailSeeder::class,
            FormateurSeeder::class,
        ]);
    }
}
