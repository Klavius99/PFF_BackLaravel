<?php

namespace Database\Seeders;

use App\Models\Formateur;
use Illuminate\Database\Seeder;

class FormateurSeeder extends Seeder
{
    public function run()
    {
        $formateurs = [
            [
                'nom' => 'Formateur',
                'prenom' => 'Un',
                'email' => 'formateur1@isepdiamniadio.edu.sn',
                'specialite' => 'Développement Web',
                'est_inscrit' => false
            ],
            [
                'nom' => 'Formateur',
                'prenom' => 'Deux',
                'email' => 'formateur2@isepdiamniadio.edu.sn',
                'specialite' => 'Base de données',
                'est_inscrit' => false
            ],
            [
                'nom' => 'Formateur',
                'prenom' => 'Trois',
                'email' => 'formateur3@isepdiamniadio.edu.sn',
                'specialite' => 'Intelligence Artificielle',
                'est_inscrit' => false
            ],
        ];

        foreach ($formateurs as $formateur) {
            Formateur::create($formateur);
        }
    }
}
