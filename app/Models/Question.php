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

    public function test() {
        return $this->belongsTo(Test::class, 'id_test');
    }

    public function options() {
        return $this->hasMany(OptionQcm::class, 'id_question');
    }

    public function reponsesEtudiants() {
        return $this->hasMany(ReponseEtudiant::class, 'id_question');
    }
}
