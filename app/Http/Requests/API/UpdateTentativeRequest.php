<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTentativeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'heure_soumission' => 'sometimes|date|after_or_equal:heure_debut',
            'note_obtenue' => 'sometimes|numeric|min:0',
            'est_noter' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'heure_soumission.after_or_equal' => 'L\'heure de soumission doit être après l\'heure de début.',
            'note_obtenue.numeric' => 'La note obtenue doit être un nombre.',
            'note_obtenue.min' => 'La note obtenue ne peut pas être négative.',
            'est_noter.boolean' => 'La valeur doit être vrai ou faux.',
        ];
    }
}
