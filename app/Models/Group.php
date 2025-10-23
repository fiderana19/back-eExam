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
    ];

    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'id_groupe');
    }

    public function groups()
    {
        return $this->hasMany(Annonce::class, 'id_groupe');
    }
}
