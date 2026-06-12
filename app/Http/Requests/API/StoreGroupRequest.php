<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom_groupe' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'nom_groupe.required' => 'Le nom du groupe est obligatoire.',
            'nom_groupe.max' => 'Le nom du groupe ne doit pas dépasser :max caractères.',
        ];
    }
}
