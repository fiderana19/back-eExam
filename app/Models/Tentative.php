<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

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

    // ─── Relations ────────────────────────────────────────────────

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function test()
    {
        return $this->belongsTo(Test::class, 'id_test', 'id_test');
    }

    public function reponses()
    {
        return $this->hasMany(Reponse::class, 'id_tentative');
    }

    // ─── Vérifications ────────────────────────────────────────────

    public function isByUser(Utilisateur $user): bool
    {
        return (int) $this->id_utilisateur === (int) $user->id_utilisateur;
    }

    public static function existsForTestAndUser(int $testId, int $userId): bool
    {
        return self::where('id_test', $testId)
                   ->where('id_utilisateur', $userId)
                   ->exists();
    }

    // ─── Actions ──────────────────────────────────────────────────

    public function start(): void
    {
        $this->heure_debut = Carbon::now();
        $this->est_noter = false;
        $this->save();
    }

    public function submit(): void
    {
        $this->heure_soumission = now();
        $this->save();
    }

    public function recalculateScore(): void
    {
        $this->note_obtenue = $this->reponses()->sum('score_question');
        $this->save();
    }

    public function checkAndMarkAsNoted(): void
    {
        $uncorrectedCount = $this->reponses()->where('est_corriger', false)->count();
        if ($uncorrectedCount === 0) {
            $this->est_noter = true;
            $this->save();
        }
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeUnnoted(Builder $query): void
    {
        $query->where('est_noter', false);
    }

    public function scopeNoted(Builder $query): void
    {
        $query->where('est_noter', true);
    }
}
