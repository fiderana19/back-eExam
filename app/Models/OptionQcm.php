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
        'est_correcte'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'id_question');
    }
}
