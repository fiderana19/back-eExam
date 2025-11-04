<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function question() {
        return $this->belongsTo(Question::class, 'id_question', 'id_question');
    }

    public function tentative() {
        return $this->belongsTo(Tentative::class, 'id_tentative', 'id_tentative');
    }
}
