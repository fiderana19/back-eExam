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

    public function annonces()
    {
        return $this->hasMany(Annonce::class, 'id_groupe');
    }
        
    public function test()
    {
        return $this->hasMany(Test::class, 'id_groupe');
    }
        
    public function result()
    {
        return $this->hasMany(Result::class, 'id_groupe');
    }
}
