<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TentativeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_tentative' => $this->id_tentative,
            'nom' => $this->whenLoaded('utilisateur', fn() => $this->utilisateur->nom ?? 'Inconnu'),
            'matricule' => $this->whenLoaded('utilisateur', fn() => $this->utilisateur->matricule ?? 'Non défini'),
            'heure_debut' => $this->heure_debut,
            'heure_soumission' => $this->heure_soumission,
            'note_obtenue' => $this->note_obtenue,
            'est_noter' => $this->est_noter,
        ];
    }
}
