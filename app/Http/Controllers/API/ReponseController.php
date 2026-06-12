<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreReponseRequest;
use App\Http\Requests\API\CorrigerReponseRequest;
use App\Http\Resources\API\ReponseResource;
use App\Models\Question;
use App\Models\Reponse;
use App\Models\Tentative;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReponseController extends Controller
{
    /**
     * Soumettre les réponses d'une tentative.
     *
     * Auto-corrige les questions QCM et Réponse Courte,
     * calcule la note totale et marque la tentative.
     */
    public function store(StoreReponseRequest $request): JsonResponse
    {
        $data = $request->validated();

        $questions = Question::whereIn(
            'id_question',
            collect($data['reponses'])->pluck('id_question')->unique()
        )->get()->keyBy('id_question');

        DB::beginTransaction();
        try {
            $tentative = Tentative::findOrFail($data['id_tentative']);
            $tentative->submit();

            $reponses = collect($data['reponses'])->map(function ($item) use ($questions, $tentative) {
                $reponse = new Reponse([
                    'id_tentative' => $item['id_tentative'],
                    'id_question' => $item['id_question'],
                    'reponse_texte' => $item['reponse_texte'],
                ]);

                $question = $questions->get($item['id_question']);
                if ($question && !$question->isDeveloppement()) {
                    $reponse->score_question = $item['reponse_texte'] === $question->reponse_correcte
                        ? $question->points
                        : 0;
                    $reponse->est_corriger = true;
                }

                return $reponse;
            });

            $tentative->reponses()->saveMany($reponses);
            $tentative->recalculateScore();
            $tentative->checkAndMarkAsNoted();

            DB::commit();

            return response()->json($tentative->id, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de la soumission des réponses.'], 500);
        }
    }

    /**
     * Modifier le texte d'une réponse (par l'étudiant propriétaire).
     */
    public function updateTexte($id): JsonResponse
    {
        request()->validate(['reponse_texte' => 'required|string']);

        $reponse = Reponse::findOrFail($id);

        if (auth()->user()->isEtudiant()) {
            $reponse->update(['reponse_texte' => request()->reponse_texte]);

            return response()->json([
                'message' => 'Réponse modifiée avec succès.',
                'data' => $reponse,
            ]);
        }

        return response()->json(['message' => 'Accès refusé.'], 403);
    }

    /**
     * Corriger une réponse de type développement.
     *
     * Met à jour le score, recalcule la note de la tentative
     * et marque la tentative comme notée si tout est corrigé.
     */
    public function corrigerReponse(CorrigerReponseRequest $request, int $id): JsonResponse
    {
        $reponse = Reponse::with('tentative')->findOrFail($id);

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
            $tentative->recalculateScore();
            $tentative->checkAndMarkAsNoted();

            DB::commit();

            return response()->json([
                'message' => 'Réponse corrigée et score de tentative mis à jour avec succès.',
                'data' => new ReponseResource($reponse),
                'nouveau_score_tentative' => $tentative->note_obtenue,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de la correction.'], 500);
        }
    }

    /**
     * Réponses non corrigées d'un test.
     */
    public function getByTest(int $id_test): JsonResponse
    {
        $reponses = Reponse::whereHas('tentative', fn($q) => $q->where('id_test', $id_test))
            ->uncorrected()
            ->with(['tentative', 'question'])
            ->get();

        return response()->json($reponses);
    }

    /**
     * Détail d'une réponse.
     */
    public function show(int $id): JsonResponse
    {
        $reponse = Reponse::with('question:id_question,texte_question,points')
            ->findOrFail($id);

        return response()->json($reponse);
    }

    /**
     * Toutes les réponses non corrigées.
     */
    public function getNonCorrigees(): JsonResponse
    {
        $reponses = Reponse::uncorrected()
            ->with([
                'question:id_question,texte_question,points',
                'tentative:id_tentative,id_test',
            ])
            ->get();

        return response()->json($reponses);
    }
}
