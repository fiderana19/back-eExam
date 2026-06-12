<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Annonce extends Model
{
    use HasFactory;

    protected $table = 'annonces';
    protected $primaryKey = 'id_annonce';
    protected $fillable = ['id_utilisateur', 'id_groupe', 'titre_annonce', 'texte_annonce', 'creation_annonce'];

    // ─── Relations ────────────────────────────────────────────────

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'id_groupe');
    }

    // ─── Vérifications ────────────────────────────────────────────

    public function isOwnedBy(Utilisateur $user): bool
    {
        return (int) $this->id_utilisateur === (int) $user->id_utilisateur;
    }

    public function isOwnedByOrAdmin(Utilisateur $user): bool
    {
        return $user->isAdmin() || $this->isOwnedBy($user);
    }
}
