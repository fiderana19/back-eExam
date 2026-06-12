<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnonceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_annonce' => $this->id_annonce,
            'id_utilisateur' => $this->id_utilisateur,
            'id_groupe' => $this->id_groupe,
            'titre_annonce' => $this->titre_annonce,
            'texte_annonce' => $this->texte_annonce,
            'creation_annonce' => $this->creation_annonce,
            'utilisateur' => $this->whenLoaded('utilisateur', fn() => [
                'id_utilisateur' => $this->utilisateur->id_utilisateur,
                'nom' => $this->utilisateur->nom,
            ]),
            'group' => $this->whenLoaded('group', fn() => [
                'id_groupe' => $this->group->id_groupe,
                'nom_groupe' => $this->group->nom_groupe,
            ]),
        ];
    }
}
