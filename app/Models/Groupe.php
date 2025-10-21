<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groupes';
    protected $primaryKey = 'id_groupe';
    public $timestamps = false;

    protected $fillable = [
        'nom_groupe',
        'description',
        'id_utilisateur'
    ];

    public function createur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }
}
