<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Formateur extends Model
{
    protected $table = 'formateurs';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'specialite',
        'description',
        'est_inscrit'
    ];

    protected $casts = [
        'est_inscrit' => 'boolean',
    ];
}
