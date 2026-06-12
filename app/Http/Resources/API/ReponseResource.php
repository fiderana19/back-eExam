<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_reponse' => $this->id_reponse,
            'id_tentative' => $this->id_tentative,
            'id_question' => $this->id_question,
            'reponse_texte' => $this->reponse_texte,
            'score_question' => $this->score_question,
            'est_corriger' => $this->est_corriger,
            'question' => $this->whenLoaded('question', fn() => [
                'id_question' => $this->question->id_question,
                'texte_question' => $this->question->texte_question,
                'points' => $this->question->points,
            ]),
        ];
    }
}
