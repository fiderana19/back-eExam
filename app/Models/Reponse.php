<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Reponse extends Model
{
    use HasFactory;

    protected $table = 'reponses_etudiants';
    protected $primaryKey = 'id_reponse';
    protected $fillable = [
        'id_question',
        'id_tentative',
        'reponse_texte',
        'score_question',
        'est_corriger',
    ];

    // ─── Relations ────────────────────────────────────────────────

    public function question()
    {
        return $this->belongsTo(Question::class, 'id_question', 'id_question');
    }

    public function tentative()
    {
        return $this->belongsTo(Tentative::class, 'id_tentative', 'id_tentative');
    }

    // ─── Vérifications ────────────────────────────────────────────

    public function isAutoCorrectable(): bool
    {
        return $this->relationLoaded('question') && $this->question
            ? !$this->question->isDeveloppement()
            : false;
    }

    // ─── Actions ──────────────────────────────────────────────────

    public function autoCorrect(): void
    {
        if (!$this->isAutoCorrectable() || !$this->question) {
            return;
        }

        $this->score_question = $this->reponse_texte === $this->question->reponse_correcte
            ? $this->question->points
            : 0;
        $this->est_corriger = true;
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeUncorrected(Builder $query): void
    {
        $query->where('est_corriger', false);
    }
}
