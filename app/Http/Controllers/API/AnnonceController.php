<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreAnnonceRequest;
use App\Http\Requests\API\UpdateAnnonceRequest;
use App\Http\Resources\API\AnnonceResource;
use App\Models\Annonce;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class AnnonceController extends Controller
{
    /**
     * Créer une annonce (admin ou enseignant).
     */
    public function store(StoreAnnonceRequest $request): JsonResponse
    {
        $annonce = Annonce::create([
            'id_utilisateur' => $request->user()->id_utilisateur,
            ...$request->validated(),
        ]);

        return response()->json([
            'message' => 'Annonce créée avec succès.',
            'data' => new AnnonceResource($annonce),
        ], 201);
    }

    /**
     * Annonces d'un groupe (triées par date décroissante).
     */
    public function getByGroupe(int $id_groupe): AnonymousResourceCollection
    {
        $annonces = Annonce::where('id_groupe', $id_groupe)
            ->orderByDesc('created_at')
            ->with(['group', 'utilisateur'])
            ->get();

        return AnnonceResource::collection($annonces);
    }

    /**
     * Détail d'une annonce.
     */
    public function show(Annonce $annonce): AnnonceResource
    {
        return new AnnonceResource($annonce);
    }

    /**
     * Trois dernières annonces d'un groupe.
     */
    public function lastByGroupe(int $id_groupe): AnonymousResourceCollection
    {
        $annonces = Annonce::where('id_groupe', $id_groupe)
            ->with(['utilisateur:id_utilisateur,nom', 'group:id_groupe,nom_groupe'])
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        return AnnonceResource::collection($annonces);
    }

    /**
     * Annonces récentes publiées par un utilisateur.
     */
    public function lastByUser(int $id_utilisateur): AnonymousResourceCollection
    {
        $annonces = Annonce::where('id_utilisateur', $id_utilisateur)
            ->orderByDesc('created_at')
            ->with(['group', 'utilisateur'])
            ->get();

        return AnnonceResource::collection($annonces);
    }

    /**
     * Modifier une annonce (créateur ou admin).
     */
    public function update(UpdateAnnonceRequest $request, int $id): JsonResponse
    {
        $annonce = Annonce::findOrFail($id);

        if (!$annonce->isOwnedByOrAdmin($request->user())) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $annonce->update($request->validated());

        return response()->json([
            'message' => 'Annonce modifiée avec succès.',
            'data' => new AnnonceResource($annonce),
        ]);
    }

    /**
     * Supprimer une annonce (créateur ou admin).
     */
    public function destroy(int $id): JsonResponse
    {
        $annonce = Annonce::findOrFail($id);
        $user = Auth::user();

        if (!$annonce->isOwnedByOrAdmin($user)) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $annonce->delete();

        return response()->json(['message' => 'Annonce supprimée avec succès.']);
    }
}
