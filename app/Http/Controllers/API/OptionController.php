<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreOptionRequest;
use App\Models\OptionQcm;
use Illuminate\Http\JsonResponse;

class OptionController extends Controller
{
    /**
     * Créer une option QCM.
     *
     * Si l'option est correcte, la réponse correcte de la question
     * est synchronisée automatiquement.
     */
    public function store(StoreOptionRequest $request): JsonResponse
    {
        $option = OptionQcm::create($request->validated());
        $option->load('question');
        $option->syncQuestionCorrectAnswer();

        return response()->json([
            'message' => 'Option créée avec succès.',
            'data' => $option,
        ], 201);
    }

    /**
     * Options d'une question.
     */
    public function getByQuestion(int $id_question): JsonResponse
    {
        $options = OptionQcm::where('id_question', $id_question)->get();
        return response()->json($options);
    }

    /**
     * Supprimer une option.
     */
    public function destroy(int $id): JsonResponse
    {
        $option = OptionQcm::find($id);

        if (!$option) {
            return response()->json(['message' => 'Option introuvable.'], 404);
        }

        $option->delete();

        return response()->json(['message' => 'Option supprimée avec succès.']);
    }
}
