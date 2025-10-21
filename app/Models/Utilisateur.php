<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Utilisateur extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'utilisateurs';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nom',
        'email',
        'matricule',
        'password',
        'role',
        'est_valider',
    ];

    protected $hidden = ['password'];

    // Relations
    public function groupes() {
        return $this->hasMany(Groupe::class, 'id_utilisateur');
    }

    public function annonces() {
        return $this->hasMany(Annonce::class, 'id_utilisateur');
    }

    public function tests() {
        return $this->hasMany(Test::class, 'id_utilisateur');
    }

    public function tentatives() {
        return $this->hasMany(Tentative::class, 'id_utilisateur');
    }

    public function resultats() {
        return $this->hasMany(Resultat::class, 'id_utilisateur');
    }

    // JWT
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return [];
    }

    // Vérifie si l'utilisateur est approuvé
    public function isApproved()
    {
        return $this->est_valider;
    }

    // Vérifie le rôle
    public function hasRole($role)
    {
        return $this->role === $role;
    }
}