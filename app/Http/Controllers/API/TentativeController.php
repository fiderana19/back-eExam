<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tentative;
use App\Models\Test;
use Illuminate\Support\Facades\Auth;

class TentativeController extends Controller
{
    /**
     * Créer une nouvelle tentative
     * Accessible uniquement aux étudiants
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'etudiant') {
            return response()->json(['message' => 'Accès refusé. Seuls les étudiants peuvent créer une tentative.'], 403);
        }

        $validated = $request->validate([
            'id_test' => 'required|exists:test,id_test',
            'heure_debut' => 'required|date',
            'heure_soumission' => 'nullable|date|after_or_equal:heure_debut',
        ]);

        $validated['id_utilisateur'] = $user->id_utilisateur;
        $validated['est_noter'] = false;

        $tentative = Tentative::create($validated);

        return response()->json([
            'message' => 'Tentative créée avec succès.',
            'tentative' => $tentative
        ], 201);
    }

    /**
     * Modifier une tentative
     * exemple mise à jour de la note, heure de soumission ou statut
     */
    public function update(Request $request, $id_tentative)
    {
        $user = Auth::user();
        $tentative = Tentative::findOrFail($id_tentative);

        if (!in_array($user->role, ['admin', 'enseignant']) && $tentative->id_utilisateur !== $user->id_utilisateur) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'heure_soumission' => 'sometimes|date|after_or_equal:heure_debut',
            'note_obtenue' => 'sometimes|numeric|min:0',
            'est_noter' => 'sometimes|boolean',
        ]);

        $tentative->update($validated);

        return response()->json([
            'message' => 'Tentative mise à jour avec succès.',
            'tentative' => $tentative
        ]);
    }

    /**
     * Récupérer toutes les tentatives d’un test avec le nom et le matricule de chaque utilisateur
     * Accessible par admin et enseignant
     */
    public function getByTest($id_test)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'enseignant'])) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $tentatives = Tentative::with(['utilisateur:id_utilisateur,nom,matricule'])
            ->where('id_test', $id_test)
            ->get();

        $data = $tentatives->map(function ($t) {
            return [
                'id_tentative' => $t->id_tentative,
                'nom' => $t->utilisateur->nom ?? 'Inconnu',
                'matricule' => $t->utilisateur->matricule ?? 'Non défini',
                'heure_debut' => $t->heure_debut,
                'heure_soumission' => $t->heure_soumission,
                'note_obtenue' => $t->note_obtenue,
                'est_noter' => $t->est_noter,
            ];
        });

        return response()->json($data);
    }
}
