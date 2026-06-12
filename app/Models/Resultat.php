<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Resultat extends Model
{
    use HasFactory;

    protected $table = 'resultats';
    protected $primaryKey = 'id_resultat';
    public $timestamps = true;

    protected $fillable = [
        'id_utilisateur',
        'id_groupe',
        'titre_resultat',
        'fichier_resultat',
    ];

    // ─── Relations ────────────────────────────────────────────────

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function groupe()
    {
        return $this->belongsTo(Group::class, 'id_groupe');
    }

    // ─── Gestion du fichier ───────────────────────────────────────

    public function hasFile(): bool
    {
        return !empty($this->fichier_resultat);
    }

    public function fileExists(): bool
    {
        return $this->hasFile() && Storage::disk('public')->exists($this->fichier_resultat);
    }

    public function deleteFile(): void
    {
        if ($this->fileExists()) {
            Storage::disk('public')->delete($this->fichier_resultat);
        }
    }
}
