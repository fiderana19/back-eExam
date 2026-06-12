<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class StoreOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isInRole(['admin', 'enseignant']);
    }

    public function rules(): array
    {
        return [
            'id_question' => 'required|exists:questions,id_question',
            'texte_option' => 'required|string',
            'est_correcte' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'id_question.required' => 'La question est obligatoire.',
            'id_question.exists' => 'La question sélectionnée n\'existe pas.',
            'texte_option.required' => 'Le texte de l\'option est obligatoire.',
            'est_correcte.required' => 'Veuillez préciser si l\'option est correcte.',
            'est_correcte.boolean' => 'La valeur doit être vrai ou faux.',
        ];
    }
}
