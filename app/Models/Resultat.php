<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resultat extends Model
{
    use HasFactory;

    protected $table = 'resultats';
    protected $primaryKey = 'id_resultat';
    public $timestamps = false;

    protected $fillable = [
        'id_utilisateur',
        'id_groupe',
        'titre_resultat',
        'fichier_resultat',
        'date_publication',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function groupe()
    {
        return $this->belongsTo(Groupe::class, 'id_groupe');
    }
}
