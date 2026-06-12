<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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

    // ─── Relations ────────────────────────────────────────────────

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

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeVisible(Builder $query): void
    {
        $query->where('nom_groupe', '!=', 'ADMIN');
    }

    // ─── Vérifications ────────────────────────────────────────────

    public function isAdminGroup(): bool
    {
        return $this->nom_groupe === 'ADMIN';
    }

    public function isEnseignantGroup(): bool
    {
        return $this->nom_groupe === 'ENSEIGNANT';
    }
}
