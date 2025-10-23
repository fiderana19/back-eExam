<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnonceController extends Controller
{
    /**
     * Créer une annonce
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Seuls admin et enseignant peuvent créer une annonce
        if (!in_array($user->role, ['admin', 'enseignant'])) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $request->validate([
            'id_groupe' => 'required|exists:groupes,id_groupe',
            'titre_annonce' => 'required|string|max:255',
            'texte_annonce' => 'required|string',
        ]);

        $annonce = Annonce::create([
            'id_utilisateur' => $user->id_utilisateur,
            'id_groupe' => $request->id_groupe,
            'titre_annonce' => $request->titre_annonce,
            'texte_annonce' => $request->texte_annonce,
        ]);

        return response()->json([
            'message' => 'Annonce créée avec succès.',
            'data' => $annonce,
        ], 201);
    }

    /**
     * Récupérer toutes les annonces d’un groupe
     */
    public function getByGroupe($id_groupe)
    {
        $annonces = Annonce::where('id_groupe', $id_groupe)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($annonces);
    }

    /**
     * Récupérer une annonce par son ID
     */
    public function show(Annonce $annonce)
    {
        return response()->json($annonce);
    }

    /**
     * Récupérer les 3 dernières annonces d’un groupe
     */
    public function lastByGroupe($id_groupe)
    {
        $annonces = Annonce::where('id_groupe', $id_groupe)->with(['utilisateurs:id_utilisateur,nom', 'groupes:id_groupe,nom_groupe'])
            ->orderByDesc('created_at')
            ->take(3)
            ->get().compact('nom_groupe');

        return response()->json($annonces);
    }

    /**
     * Récupérer les 3 dernières annonces publiées par un utilisateur
     */
    public function lastByUser($id_utilisateur)
    {
        $annonces = Annonce::where('id_utilisateur', $id_utilisateur)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($annonces);
    }

    /**
     * Modifier une annonce
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $annonce = Annonce::findOrFail($id);

        // Seul le créateur (ou admin) peut modifier
        if ($user->id_utilisateur !== $annonce->id_utilisateur && $user->role !== 'admin') {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $request->validate([
            'titre_annonce' => 'sometimes|string|max:255',
            'texte_annonce' => 'sometimes|string',
        ]);

        $annonce->update($request->only(['titre_annonce', 'texte_annonce']));

        return response()->json([
            'message' => 'Annonce modifiée avec succès.',
            'data' => $annonce,
        ]);
    }

    /**
     * Supprimer une annonce
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $annonce = Annonce::findOrFail($id);

        // Seul le créateur ou admin peut supprimer
        if ($user->id_utilisateur !== $annonce->id_utilisateur) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $annonce->delete();

        return response()->json(['message' => 'Annonce supprimée avec succès.']);
    }
}
