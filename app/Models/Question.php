<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $table = 'questions';
    protected $primaryKey = 'id_question';
    protected $fillable = [
        'id_test',
        'texte_question',
        'type_question',
        'points',
        'reponse_correcte',
    ];

    // ─── Relations ────────────────────────────────────────────────

    public function test()
    {
        return $this->belongsTo(Test::class, 'id_test');
    }

    public function options()
    {
        return $this->hasMany(OptionQcm::class, 'id_question');
    }

    // ─── Types ────────────────────────────────────────────────────

    public function isDeveloppement(): bool
    {
        return $this->type_question === 'developpement';
    }

    public function isQcm(): bool
    {
        return $this->type_question === 'QCM';
    }

    public function isReponseCourte(): bool
    {
        return $this->type_question === 'Réponse Courte';
    }

    // ─── Logique métier ───────────────────────────────────────────

    public function configurePoints(): void
    {
        $this->points = $this->isDeveloppement() ? 2 : 1;
        if ($this->isDeveloppement()) {
            $this->reponse_correcte = null;
        }
    }
}
