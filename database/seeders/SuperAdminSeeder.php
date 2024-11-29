<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'username' => 'superadmin',
            'email' => 'superadmin@isepdiamniadio.edu.sn',
            'password' => Hash::make('SuperAdmin@123'),
            'role' => 'super_admin',
            'status' => true,
        ]);
    }
}
