<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class StoreTentativeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isEtudiant();
    }

    public function rules(): array
    {
        return [
            'id_test' => 'required|exists:tests,id_test',
        ];
    }

    public function messages(): array
    {
        return [
            'id_test.required' => 'Le test est obligatoire.',
            'id_test.exists' => 'Le test sélectionné n\'existe pas.',
        ];
    }
}
