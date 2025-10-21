<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OptionQcm;
use App\Models\Question;

class OptionController extends Controller
{
    // Créer une option
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_question' => 'required|exists:question,id_question',
            'texte_option' => 'required|string',
            'est_correcte' => 'required|boolean',
        ]);

        $option = OptionQcm::create($validated);

        return response()->json([
            'message' => 'Option créée avec succès.',
            'data' => $option
        ], 201);
    }

    // Récupérer les options par l’ID d’une question
    public function getByQuestion($id_question)
    {
        $options = OptionQcm::where('id_question', $id_question)->get();

        if ($options->isEmpty()) {
            return response()->json(['message' => 'Aucune option trouvée pour cette question.'], 404);
        }

        return response()->json([
            'message' => 'Liste des options.',
            'data' => $options
        ]);
    }

    // Supprimer une option
    public function destroy($id)
    {
        $option = OptionQcm::find($id);

        if (!$option) {
            return response()->json(['message' => 'Option introuvable.'], 404);
        }

        $option->delete();

        return response()->json(['message' => 'Option supprimée avec succès.']);
    }
}
