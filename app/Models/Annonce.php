<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Annonce extends Model
{
    use HasFactory;

    protected $table = 'annonces';
    protected $primaryKey = 'id_annonce';
    protected $fillable = ['id_utilisateur', 'id_groupe' , 'titre_annonce', 'texte_annonce', 'creation_annonce'];

    public function utilisateur() {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function group() {
        return $this->belongsTo(Group::class, 'id_groupe');
    }
}