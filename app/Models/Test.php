<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $table = 'tests';
    protected $primaryKey = 'id_test';
    protected $fillable = [
        'id_utilisateur',
        'id_groupe',
        'titre',
        'description',
        'duree_minutes',
        'max_questions',
        'note_max',
        'date_declechement',
        'status',
    ];

    public function createur() {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function groupe() {
        return $this->belongsTo(Groupe::class, 'id_groupe');
    }

    public function questions() {
        return $this->hasMany(Question::class, 'id_test');
    }

    public function tentatives() {
        return $this->hasMany(Tentative::class, 'id_test');
    }
}
