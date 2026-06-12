<?php

namespace App\Models;

use App\Enums\QuestionType;
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

    protected function casts(): array
    {
        return [
            'type_question' => QuestionType::class,
        ];
    }

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
        return $this->type_question === QuestionType::Developpement;
    }

    public function isQcm(): bool
    {
        return $this->type_question === QuestionType::Qcm;
    }

    public function isReponseCourte(): bool
    {
        return $this->type_question === QuestionType::ReponseCourte;
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
