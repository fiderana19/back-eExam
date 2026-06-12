<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OptionQcm extends Model
{
    use HasFactory;

    protected $table = 'options_qcm';
    protected $primaryKey = 'id_option';

    protected $fillable = [
        'id_question',
        'texte_option',
        'est_correcte',
    ];

    // ─── Relations ────────────────────────────────────────────────

    public function question()
    {
        return $this->belongsTo(Question::class, 'id_question');
    }

    // ─── Actions ──────────────────────────────────────────────────

    public function syncQuestionCorrectAnswer(): void
    {
        if (!$this->est_correcte) {
            return;
        }

        $this->relationLoaded('question')
            ? $this->question->update(['reponse_correcte' => $this->texte_option])
            : Question::where('id_question', $this->id_question)
                      ->update(['reponse_correcte' => $this->texte_option]);
    }
}
