<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreResultatRequest;
use App\Models\Resultat;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ResultatController extends Controller
{
    /**
     * Créer un résultat avec fichier optionnel.
     */
    public function store(StoreResultatRequest $request): JsonResponse
    {
        $path = null;
        if ($request->hasFile('fichier_resultat')) {
            $path = $request->file('fichier_resultat')
                ->storeAs('resultats', $request->file('fichier_resultat')->getClientOriginalName(), 'public');
        }

        $resultat = Resultat::create([
            'id_groupe' => $request->id_groupe,
            'titre_resultat' => $request->titre_resultat,
            'fichier_resultat' => $path,
        ]);

        return response()->json($resultat, 201);
    }

    /**
     * Résultats d'un groupe.
     */
    public function getByGroupe(int $id_groupe): JsonResponse
    {
        $resultats = Resultat::where('id_groupe', $id_groupe)->get();
        return response()->json($resultats);
    }

    /**
     * Tous les résultats (admin uniquement, middleware vérifié dans le route).
     */
    public function getAll(): JsonResponse
    {
        $resultats = Resultat::with('groupe')->get();
        return response()->json($resultats);
    }

    /**
     * Supprimer un résultat (admin uniquement).
     */
    public function destroy(int $id): JsonResponse
    {
        $resultat = Resultat::find($id);

        if (!$resultat) {
            return response()->json(['message' => 'Résultat introuvable.'], 404);
        }

        $resultat->deleteFile();
        $resultat->delete();

        return response()->json(['message' => 'Résultat supprimé avec succès.']);
    }

    /**
     * Télécharger le fichier d'un résultat.
     */
    public function download(int $id): JsonResponse
    {
        $resultat = Resultat::find($id);

        if (!$resultat) {
            return response()->json(['message' => 'Résultat non trouvé.'], 404);
        }

        if (!$resultat->fileExists()) {
            return response()->json(['message' => 'Fichier introuvable sur le disque.'], 404);
        }

        return Storage::disk('public')->download(
            $resultat->fichier_resultat,
            basename($resultat->fichier_resultat)
        );
    }
}
