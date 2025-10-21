<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tentative extends Model
{
    use HasFactory;

    protected $table = 'tentatives';
    protected $primaryKey = 'id_tentative';
    protected $fillable = [
        'id_utilisateur',
        'id_test',
        'heure_debut',
        'heure_soumission',
        'note_obtenue',
        'est_noter',
    ];

    public function utilisateur() {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function test() {
        return $this->belongsTo(Test::class, 'id_test');
    }

    public function reponses() {
        return $this->hasMany(ReponseEtudiant::class, 'id_tentative');
    }
}
