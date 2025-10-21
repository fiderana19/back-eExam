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
            'id_test' => 'required|exists:tests,id',
            'texte_question' => 'required|string',
            'type_question' => 'required|string',
            'points' => 'required|numeric|min:0',
            'reponse_correcte' => 'nullable|string',
        ]);

        $question = Question::create($validated);

        return response()->json([
            'message' => 'Question créée avec succès.',
            'question' => $question
        ], 201);
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
            'points' => 'sometimes|numeric|min:0',
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

        $questions = Question::where('id_test', $id_test)->inRandomOrder()->get();

        $selectedQuestions = $questions->take($test->nombre_max_questions ?? count($questions));

        $totalPoints = $selectedQuestions->sum('points');
        if ($test->points_max && $totalPoints > $test->points_max) {
            $selectedQuestions = $selectedQuestions->sortByDesc('points')
                ->takeWhile(function ($q) use (&$totalPoints, $test) {
                    $totalPoints -= $q->points;
                    return $totalPoints >= $test->points_max;
                });
        }

        return response()->json([
            'test_id' => $id_test,
            'total_points' => $selectedQuestions->sum('points'),
            'questions' => $selectedQuestions->values(),
        ]);
    }
}
