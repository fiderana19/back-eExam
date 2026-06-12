<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class CorrigerReponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isInRole(['admin', 'enseignant']);
    }

    public function rules(): array
    {
        return [
            'score_question' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'score_question.required' => 'La note est obligatoire.',
            'score_question.numeric' => 'La note doit être un nombre.',
            'score_question.min' => 'La note ne peut pas être négative.',
        ];
    }
}
