<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isInRole(['admin', 'enseignant']);
    }

    public function rules(): array
    {
        return [
            'id_test' => 'required|exists:tests,id_test',
            'texte_question' => 'required|string',
            'type_question' => 'required|string',
            'reponse_correcte' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'id_test.required' => 'Le test est obligatoire.',
            'id_test.exists' => 'Le test sélectionné n\'existe pas.',
            'texte_question.required' => 'Le texte de la question est obligatoire.',
            'type_question.required' => 'Le type de question est obligatoire.',
            'reponse_correcte.required' => 'La réponse correcte est obligatoire.',
        ];
    }
}
