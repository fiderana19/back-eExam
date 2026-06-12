<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return !$this->user()->isEtudiant();
    }

    public function rules(): array
    {
        return [
            'id_groupe' => 'required|exists:groupes,id_groupe',
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duree_minutes' => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'id_groupe.required' => 'Le groupe est obligatoire.',
            'id_groupe.exists' => 'Le groupe sélectionné n\'existe pas.',
            'titre.required' => 'Le titre du test est obligatoire.',
            'titre.max' => 'Le titre ne doit pas dépasser :max caractères.',
            'duree_minutes.required' => 'La durée du test est obligatoire.',
            'duree_minutes.integer' => 'La durée doit être un nombre entier.',
        ];
    }
}
