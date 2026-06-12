<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'email' => 'required|string|email|unique:utilisateurs,email',
            'matricule' => 'required|string|min:7',
            'password' => 'required|string|min:6',
            'id_groupe' => 'required|exists:groupes,id_groupe',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.max' => 'Le nom ne doit pas dépasser :max caractères.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être une adresse valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'matricule.required' => 'Le matricule est obligatoire.',
            'matricule.min' => 'Le matricule doit contenir au moins :min caractères.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins :min caractères.',
            'id_groupe.required' => 'Le groupe est obligatoire.',
            'id_groupe.exists' => 'Le groupe sélectionné n\'existe pas.',
        ];
    }
}
