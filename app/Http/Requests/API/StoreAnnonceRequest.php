<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnonceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isInRole(['admin', 'enseignant']);
    }

    public function rules(): array
    {
        return [
            'id_groupe' => 'required|exists:groupes,id_groupe',
            'titre_annonce' => 'required|string|max:255',
            'texte_annonce' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'id_groupe.required' => 'Le groupe est obligatoire.',
            'id_groupe.exists' => 'Le groupe sélectionné n\'existe pas.',
            'titre_annonce.required' => 'Le titre de l\'annonce est obligatoire.',
            'titre_annonce.max' => 'Le titre ne doit pas dépasser :max caractères.',
            'texte_annonce.required' => 'Le texte de l\'annonce est obligatoire.',
        ];
    }
}
