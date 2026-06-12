<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreQuestionRequest;
use App\Http\Requests\API\UpdateQuestionRequest;
use App\Models\Question;
use Illuminate\Http\JsonResponse;

class QuestionController extends Controller
{
    /**
     * Créer une question.
     *
     * Configure automatiquement les points selon le type :
     * développement (2pts) ou QCM/Réponse Courte (1pt).
     */
    public function store(StoreQuestionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $question = new Question($data);
        $question->configurePoints();
        $question->save();

        return response()->json([
            'message' => 'Question créée avec succès.',
            'question' => $question,
        ], 201);
    }

    /**
     * Détail d'une question.
     */
    public function show(Question $question): JsonResponse
    {
        return response()->json($question);
    }

    /**
     * Modifier une question.
     */
    public function update(UpdateQuestionRequest $request, int $id_question): JsonResponse
    {
        $question = Question::findOrFail($id_question);
        $question->update($request->validated());

        return response()->json([
            'message' => 'Question modifiée avec succès.',
            'question' => $question,
        ]);
    }

    /**
     * Supprimer une question.
     */
    public function destroy(int $id_question): JsonResponse
    {
        $question = Question::findOrFail($id_question);
        $question->delete();

        return response()->json(['message' => 'Question supprimée avec succès.']);
    }

    /**
     * Toutes les questions d'un test.
     */
    public function getByTest(int $id_test): JsonResponse
    {
        $questions = Question::where('id_test', $id_test)->get();
        return response()->json($questions);
    }

    /**
     * Questions aléatoires pour un test (par quotas de type).
     *
     * Accessible par tout utilisateur connecté.
     */
    public function randomByTest(int $id_test): JsonResponse
    {
        $quotas = [
            'QCM' => 5,
            'Réponse Courte' => 5,
            'Développement' => 5,
        ];

        $questions = collect();

        foreach ($quotas as $type => $limit) {
            $typeQuestions = Question::with('options')
                ->where('id_test', $id_test)
                ->where('type_question', $type)
                ->inRandomOrder()
                ->take($limit)
                ->get();

            $questions = $questions->merge($typeQuestions);
        }

        return response()->json($questions->shuffle()->values());
    }
}
