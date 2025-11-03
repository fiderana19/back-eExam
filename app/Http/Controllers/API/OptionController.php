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
            'id_question' => 'required|exists:questions,id_question',
            'texte_option' => 'required|string',
            'est_correcte' => 'required|boolean',
        ]);

        $option = OptionQcm::create($validated);
        $question = Question::findOrFail($validated['id_question']);

        if($option->est_correcte === true) {
            $question->reponse_correcte = $validated['texte_option'];
        }

        $question->save();

        return response()->json([
            'message' => 'Option créée avec succès.',
            'data' => $option
        ], 201);
    }

    // Récupérer les options par l’ID d’une question
    public function getByQuestion($id_question)
    {
        $options = OptionQcm::where('id_question', $id_question)->get();

        return response()->json($options);
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
