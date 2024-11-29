<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuthorizedTrainerEmail;

class AuthorizedTrainerEmailSeeder extends Seeder
{
    public function run()
    {
        $trainerEmails = [
            'formateur1@isepdiamniadio.edu.sn',
            'formateur2@isepdiamniadio.edu.sn',
            'formateur3@isepdiamniadio.edu.sn',
            // Ajoutez d'autres emails de formateurs ici
        ];

        foreach ($trainerEmails as $email) {
            AuthorizedTrainerEmail::create([
                'email' => $email
            ]);
        }
    }
}
