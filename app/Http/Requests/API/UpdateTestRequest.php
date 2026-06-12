<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return !$this->user()->isEtudiant();
    }

    public function rules(): array
    {
        return [
            'titre' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'duree_minutes' => 'sometimes|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'titre.max' => 'Le titre ne doit pas dépasser :max caractères.',
            'duree_minutes.integer' => 'La durée doit être un nombre entier.',
        ];
    }
}
