<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_test' => $this->id_test,
            'nom_groupe' => $this->whenLoaded('group', fn() => $this->group->nom_groupe ?? 'Aucun groupe'),
            'nom' => $this->whenLoaded('createur', fn() => $this->createur->nom ?? 'Aucun utilisateur'),
            'id_utilisateur' => $this->id_utilisateur,
            'id_groupe' => $this->id_groupe,
            'titre' => $this->titre,
            'description' => $this->description,
            'duree_minutes' => $this->duree_minutes,
            'max_questions' => $this->max_questions,
            'note_max' => $this->note_max,
            'date_declechement' => $this->date_declechement,
            'status' => $this->status,
        ];
    }
}
