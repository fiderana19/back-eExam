<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Utilisateur extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'utilisateurs';
    protected $primaryKey = 'id_utilisateur';

    protected $fillable = [
        'id_groupe',
        'nom',
        'email',
        'matricule',
        'password',
        'role',
        'est_valider',
    ];

    protected $hidden = ['password'];

    // ─── Relations ────────────────────────────────────────────────

    public function groupe()
    {
        return $this->belongsTo(Group::class, 'id_groupe', 'id_groupe');
    }

    public function annonces()
    {
        return $this->hasMany(Annonce::class, 'id_utilisateur');
    }

    public function tests()
    {
        return $this->hasMany(Test::class, 'id_utilisateur');
    }

    public function tentatives()
    {
        return $this->hasMany(Tentative::class, 'id_utilisateur');
    }

    // ─── Vérifications de rôle ────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEnseignant(): bool
    {
        return $this->role === 'enseignant';
    }

    public function isEtudiant(): bool
    {
        return $this->role === 'etudiant';
    }

    public function isInRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    // ─── Statut du compte ─────────────────────────────────────────

    public function isApproved(): bool
    {
        return (bool) $this->est_valider;
    }

    public function approve(): void
    {
        $this->est_valider = true;
        $this->save();
    }

    public function block(): void
    {
        $this->est_valider = false;
        $this->save();
    }

    // ─── Logique d'inscription ────────────────────────────────────

    public static function determineRoleFromGroup(int $idGroupe): string
    {
        $group = Group::findOrFail($idGroupe);
        return $group->nom_groupe === 'ENSEIGNANT' ? 'enseignant' : 'etudiant';
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopePending(Builder $query): void
    {
        $query->where('est_valider', false)
              ->whereIn('role', ['etudiant', 'enseignant']);
    }

    public function scopeNonAdmin(Builder $query): void
    {
        $query->where('role', '!=', 'admin');
    }

    // ─── JWT ──────────────────────────────────────────────────────

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'id' => $this->getKey(),
            'role' => $this->role,
            'id_groupe' => $this->id_groupe,
        ];
    }
}
