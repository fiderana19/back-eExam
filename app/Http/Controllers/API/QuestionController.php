<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Test;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    /**
     * Créer une nouvelle question
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'enseignant'])) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'id_test' => 'required|exists:tests,id_test',
            'texte_question' => 'required|string',
            'type_question' => 'required|string',
            'reponse_correcte' => 'required|string',
        ]);

        if($validated['type_question'] === 'developpement') {
            $validated['points'] = 2;
            $validated['reponse_correcte'] = null;
        } else {
            $validated['points'] = 1;
        }

        $question = Question::create($validated);

        return response()->json([
            'message' => 'Question créée avec succès.',
            'question' => $question
        ], 201);
    }

    public function show(Question $question)
    {
        if (!$question) {
            return response()->json(['message' => 'Question introuvable'], 404);
        }
    
        return response()->json($question);
    }
    /**
     * Modifier une question
     */
    public function update(Request $request, $id_question)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'enseignant'])) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $question = Question::findOrFail($id_question);

        $validated = $request->validate([
            'texte_question' => 'sometimes|string',
            'type_question' => 'sometimes|string',
            'reponse_correcte' => 'sometimes|string|nullable',
        ]);

        $question->update($validated);

        return response()->json([
            'message' => 'Question modifiée avec succès.',
            'question' => $question
        ]);
    }

    /**
     * Supprimer une question
     */
    public function destroy($id_question)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'enseignant'])) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $question = Question::findOrFail($id_question);
        $question->delete();

        return response()->json(['message' => 'Question supprimée avec succès.']);
    }

    /**
     * Récupérer toutes les questions d’un test
     */
    public function getByTest($id_test)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'enseignant'])) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $questions = Question::where('id_test', $id_test)->get();

        return response()->json($questions);
    }

    /**
     * Récupérer des questions aléatoires pour un test
     * Accessible par tout le monde
     */
    public function randomByTest($id_test)
    {
        $test = Test::findOrFail($id_test);
        $allQuestions = collect();

        $quotas = [
            'QCM' => 5,
            'Réponse Courte' => 5,
            'Développement' => 5,
        ];

        foreach ($quotas as $type => $limit) {            
            $questionsByType = Question::with('options')
                ->where('id_test', $id_test)
                ->where('type_question', $type)
                ->inRandomOrder()
                ->take($limit)
                ->get();
                
            $allQuestions = $allQuestions->merge($questionsByType);
        }
        
        $finalQuestions = $allQuestions->shuffle();

        return response()->json($finalQuestions->values());
}
}
