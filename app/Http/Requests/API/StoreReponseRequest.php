<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isEtudiant();
    }

    public function rules(): array
    {
        return [
            'id_test' => 'required|string|exists:tests,id_test',
            'id_tentative' => 'required|string|exists:tentatives,id_tentative',
            'reponses' => 'required|array',
            'reponses.*.id_tentative' => ['required', 'string', Rule::in([$this->id_tentative])],
            'reponses.*.id_question' => 'required|string|exists:questions,id_question',
            'reponses.*.reponse_texte' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'id_test.required' => 'Le test est obligatoire.',
            'id_test.exists' => 'Le test sélectionné n\'existe pas.',
            'id_tentative.required' => 'La tentative est obligatoire.',
            'id_tentative.exists' => 'La tentative sélectionnée n\'existe pas.',
            'reponses.required' => 'Les réponses sont obligatoires.',
            'reponses.array' => 'Les réponses doivent être un tableau.',
            'reponses.*.id_tentative.required' => 'L\'identifiant de la tentative est requis pour chaque réponse.',
            'reponses.*.id_tentative.in' => 'Incohérence dans l\'identifiant de la tentative.',
            'reponses.*.id_question.required' => 'L\'identifiant de la question est requis pour chaque réponse.',
            'reponses.*.id_question.exists' => 'Une question sélectionnée n\'existe pas.',
        ];
    }
}
