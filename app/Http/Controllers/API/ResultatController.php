<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Resultat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ResultatController extends Controller
{
    /**
     * Créer un résultat
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['enseignant', 'admin'])) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $request->validate([
            'id_groupe' => 'required|integer|exists:groupes,id_groupe',
            'titre_resultat' => 'required|string|max:255',
            'fichier_resultat' => 'nullable|file|mimes:pdf,doc,docx',
        ]);

        // Upload du fichier si présent
        $path = null;
        if ($request->hasFile('fichier_resultat')) {
            $path = $request->file('fichier_resultat')->store('resultats', 'public');
        }

        $resultat = Resultat::create([
            'id_groupe' => $request->id_groupe,
            'titre_resultat' => $request->titre_resultat,
            'fichier_resultat' => $path,
            'date_publication' => now(),
        ]);

        return response()->json([
            'message' => 'Résultat créé avec succès.',
            'resultat' => $resultat
        ], 201);
    }

    /**
     * Récupérer les résultats d’un groupe
     */
    public function getByGroupe($id_groupe)
    {
        $user = Auth::user();

        if ($user->role === 'etudiant') {
            $resultats = Resultat::where('id_groupe', $id_groupe)
                ->where('id_utilisateur', $user->id_utilisateur)
                ->get();
        } else {
            $resultats = Resultat::where('id_groupe', $id_groupe)->get();
        }

        return response()->json($resultats);
    }

    /**
     * Récupérer tous les résultats
     */
    public function getAll()
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $resultats = Resultat::with(['utilisateur', 'groupe'])->get();
        return response()->json($resultats);
    }

    /**
     * Supprimer un résultat
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $resultat = Resultat::find($id);

        if (!$resultat) {
            return response()->json(['message' => 'Résultat introuvable.'], 404);
        }

        if ($user->role !== 'admin' && $user->id_utilisateur !== $resultat->id_utilisateur) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        // Supprimer le fichier si existant
        if ($resultat->fichier_resultat) {
            Storage::disk('public')->delete($resultat->fichier_resultat);
        }

        $resultat->delete();

        return response()->json(['message' => 'Résultat supprimé avec succès.']);
    }

    /**
     * Télécharger un fichier résultat
     */
    public function download($id)
    {
        $user = Auth::user();
        $resultat = Resultat::find($id);

        if (!$resultat || !$resultat->fichier_resultat) {
            return response()->json(['message' => 'Fichier non trouvé.'], 404);
        }

        if ($user->role === 'etudiant' && $resultat->id_utilisateur !== $user->id_utilisateur) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        return response()->download(storage_path('app/public/' . $resultat->fichier_resultat));
    }
}
