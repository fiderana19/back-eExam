<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Reponse;
use App\Models\Question;
use App\Models\Tentative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ReponseController extends Controller
{
    /**
     * Créer une réponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_test' => 'required|string|exists:tests,id_test',
            'id_tentative' => 'required|string|exists:tentatives,id_tentative',
            'reponses' => 'required|array',
            'reponses.*.id_tentative' => ['required', 'string', Rule::in([$request->id_tentative])],
            'reponses.*.id_question' => 'required|string|exists:questions,id_question',
            'reponses.*.reponse_texte' => 'nullable|string',
        ]);

        $user = Auth::user();
        if ($user->role !== 'etudiant') {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }
    
        $questionIds = collect($validated['reponses'])->pluck('id_question')->unique();
        $questions = Question::whereIn('id_question', $questionIds)
                          ->get()
                          ->keyBy('id_question');

        DB::beginTransaction();
        try {
            $tentative = Tentative::findOrFail($validated['id_tentative']);
            $tentative->heure_soumission = now();
            $tentative->save();
            
            $responsesToInsert = [];
                
            foreach ($validated['reponses'] as $reponseData) {
                $question = $questions->get($reponseData['id_question']);
                $score_question = 0;
                $est_corriger = 0;
            
                if ($question) {                
                    if ($question->type_question !== 'developpement') {                    
                        if ($reponseData['reponse_texte'] === $question->reponse_correcte) {                             
                            $score_question = $question->points; 
                        }
                        $est_corriger = 1;
                    }
                }
            
                $responsesToInsert[] = [
                    'id_tentative' => $reponseData['id_tentative'],
                    'id_question' => $reponseData['id_question'],
                    'reponse_texte' => $reponseData['reponse_texte'],
                    'score_question' => $score_question,
                    'est_corriger' => $est_corriger,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Reponse::insert($responsesToInsert);

            $noteTotale = Reponse::where('id_tentative', $tentative->id_tentative)
                             ->sum('score_question');
            $tentative->note_obtenue = $noteTotale;            
            $tentative->save();
                    
            $restantACorriger = $tentative->reponses()->where('est_corriger', false)->count();

            if ($restantACorriger === 0) {
                $tentative->est_noter = true;
                $tentative->save();
            }
            
            DB::commit();

            return response()->json($tentative->id, 200);
        } catch (\Exception $e) {
            DB::rollBack();
                
            return response()->json(['message' => $e], 500);
        }
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
            'score_question' => 'required|numeric|min:0', // Ajout de min:0
        ]);

        $reponse = Reponse::with('tentative')->findOrFail($id);
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'enseignant'])) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }
        
        if ($reponse->est_corriger) {
            return response()->json(['message' => 'Cette réponse a déjà été corrigée.'], 409);
        }

        DB::beginTransaction();
        try {
            $reponse->update([
                'score_question' => $request->score_question,
                'est_corriger' => true,
            ]);
            $tentative = $reponse->tentative;
            
            $nouveauScoreTotal = $tentative->reponses()->sum('score_question');

            $tentative->note_obtenue = $nouveauScoreTotal;
            $tentative->save();
            
            $restantACorriger = $tentative->reponses()->where('est_corriger', false)->count();

            if ($restantACorriger === 0) {
                $tentative->est_noter = true;
                $tentative->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Réponse corrigée et score de tentative mis à jour avec succès.',
                'data' => $reponse,
                'nouveau_score_tentative' => $tentative->note_obtenue,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Erreur lors de la correction de la réponse : " . $e);
            return response()->json(['message' => $e], 500);
        }
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

        $reponses = Reponse::whereHas('tentative', function ($queryTentative) use ($id_test) {
            $queryTentative->where('id_test', $id_test);
            
        })
        ->where('est_corriger', false)    
        ->with([
            'tentative', 
            'question'
        ])
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
