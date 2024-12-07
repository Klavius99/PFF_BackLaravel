<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuthorizedTrainerEmail;

class AuthorizedTrainerEmailSeeder extends Seeder
{
    public function run()
    {
        $trainerEmails = [
            'c.ndiaye4@isepdiamniadio.edu.sn',
            'b.mandiang4@isepdiamniadio.edu.sn',
            'm.der4@isepdiamniadio.edu.sn',
            // Ajoutez d'autres emails de formateurs ici
        ];

        foreach ($trainerEmails as $email) {
            AuthorizedTrainerEmail::create([
                'email' => $email
            ]);
        }
    }
}
