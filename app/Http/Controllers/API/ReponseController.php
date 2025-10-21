<?php

namespace App\Http\Controllers\API;

use App\Models\Reponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReponseController extends Controller
{
    /**
     * Créer une réponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_tentative' => 'required|exists:tentatives,id_tentative',
            'id_question' => 'required|exists:questions,id_question',
            'reponse_texte' => 'nullable|string',
            'score_question' => 'nullable|numeric',
        ]);

        $user = Auth::user();

        // Seuls les étudiants peuvent créer une réponse
        if ($user->role !== 'etudiant') {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $reponse = Reponse::create([
            'id_tentative' => $request->id_tentative,
            'id_question' => $request->id_question,
            'reponse_texte' => $request->reponse_texte,
            'score_question' => $request->score_question ?? 0,
            'est_corriger' => false,
        ]);

        return response()->json([
            'message' => 'Réponse créée avec succès.',
            'data' => $reponse,
        ], 201);
    }

    /**
     * Modifier uniquement le texte d'une réponse
     */
    public function updateTexte(Request $request, $id)
    {
        $request->validate([
            'reponse_texte' => 'required|string',
        ]);

        $reponse = Reponse::findOrFail($id);
        $user = Auth::user();

        // Seul l'étudiant propriétaire de la tentative peut modifier
        if ($user->role !== 'etudiant') {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $reponse->update([
            'reponse_texte' => $request->reponse_texte,
        ]);

        return response()->json([
            'message' => 'Réponse modifiée avec succès.',
            'data' => $reponse,
        ]);
    }

    /**
     * Modifier le score d'une réponse et la marquer comme corrigée
     */
    public function corrigerReponse(Request $request, $id)
    {
        $request->validate([
            'score_question' => 'required|numeric',
        ]);

        $reponse = Reponse::findOrFail($id);
        $user = Auth::user();

        // Seuls admin ou enseignant peuvent corriger
        if (!in_array($user->role, ['admin', 'enseignant'])) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $reponse->update([
            'score_question' => $request->score_question,
            'est_corriger' => true,
        ]);

        return response()->json([
            'message' => 'Réponse corrigée avec succès.',
            'data' => $reponse,
        ]);
    }

    /**
     * Récupérer les réponses liées à un test donné
     */
    public function getByTest($id_test)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'enseignant'])) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $reponses = Reponse::select(
                'reponses.id_reponse',
                'reponses.id_tentative',
                'reponses.id_question',
                'reponses.reponse_texte',
                'reponses.score_question',
                'reponses.est_corriger',
                'questions.texte_question',
                'questions.points',
                'tentatives.id_test'
            )
            ->join('tentatives', 'tentatives.id_tentative', '=', 'reponses.id_tentative')
            ->join('questions', 'questions.id_question', '=', 'reponses.id_question')
            ->where('tentatives.id_test', $id_test)
            ->get();

        return response()->json($reponses);
    }

    /**
     * Récupérer une seule réponse par son ID
     */
    public function show($id)
    {
        $reponse = Reponse::with(['question:id_question,texte_question,points'])->findOrFail($id);

        return response()->json($reponse);
    }

    /**
     * Récupérer toutes les réponses non corrigées (est_corriger = false)
     */
    public function getNonCorrigees()
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'enseignant'])) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $reponses = Reponse::where('est_corriger', false)
            ->with(['question:id_question,texte_question,points', 'tentative:id_tentative,id_test'])
            ->get();

        return response()->json($reponses);
    }
}
