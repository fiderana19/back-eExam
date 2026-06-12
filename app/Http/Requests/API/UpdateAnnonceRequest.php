<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnonceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre_annonce' => 'sometimes|string|max:255',
            'texte_annonce' => 'sometimes|string',
        ];
    }

    public function messages(): array
    {
        return [
            'titre_annonce.max' => 'Le titre ne doit pas dépasser :max caractères.',
        ];
    }
}
