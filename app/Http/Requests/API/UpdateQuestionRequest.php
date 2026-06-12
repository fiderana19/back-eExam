<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isInRole(['admin', 'enseignant']);
    }

    public function rules(): array
    {
        return [
            'texte_question' => 'sometimes|string',
            'type_question' => 'sometimes|string',
            'reponse_correcte' => 'sometimes|string|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'texte_question.string' => 'Le texte de la question doit être une chaîne de caractères.',
            'type_question.string' => 'Le type de question doit être une chaîne de caractères.',
            'reponse_correcte.string' => 'La réponse correcte doit être une chaîne de caractères.',
        ];
    }
}
