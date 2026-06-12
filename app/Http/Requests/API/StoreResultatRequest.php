<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class StoreResultatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isInRole(['enseignant', 'admin']);
    }

    public function rules(): array
    {
        return [
            'id_groupe' => 'required|integer|exists:groupes,id_groupe',
            'titre_resultat' => 'required|string|max:255',
            'fichier_resultat' => 'nullable|file|mimes:pdf,doc,docx',
        ];
    }

    public function messages(): array
    {
        return [
            'id_groupe.required' => 'Le groupe est obligatoire.',
            'id_groupe.integer' => 'Le groupe doit être un identifiant valide.',
            'id_groupe.exists' => 'Le groupe sélectionné n\'existe pas.',
            'titre_resultat.required' => 'Le titre du résultat est obligatoire.',
            'titre_resultat.max' => 'Le titre ne doit pas dépasser :max caractères.',
            'fichier_resultat.file' => 'Le fichier doit être un fichier valide.',
            'fichier_resultat.mimes' => 'Le fichier doit être au format PDF, DOC ou DOCX.',
        ];
    }
}
