<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreTentativeRequest;
use App\Http\Requests\API\UpdateTentativeRequest;
use App\Http\Resources\API\TentativeResource;
use App\Models\Tentative;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TentativeController extends Controller
{
    /**
     * Créer une tentative pour un étudiant.
     *
     * Vérifie qu'il n'existe pas déjà une tentative pour ce test.
     */
    public function store(StoreTentativeRequest $request): JsonResponse
    {
        $user = $request->user();

        if (Tentative::existsForTestAndUser($request->id_test, $user->id_utilisateur)) {
            return response()->json([
                'message' => 'Tentative déjà enregistrée. Cet étudiant a déjà commencé ce test.',
            ], 409);
        }

        $tentative = Tentative::create([
            'id_test' => $request->id_test,
            'id_utilisateur' => $user->id_utilisateur,
        ]);
        $tentative->start();

        return response()->json($tentative, 201);
    }

    /**
     * Modifier une tentative (note, soumission, statut).
     */
    public function update(UpdateTentativeRequest $request, int $id_tentative): JsonResponse
    {
        $tentative = Tentative::findOrFail($id_tentative);
        $user = $request->user();

        if (!$user->isInRole(['admin', 'enseignant']) && !$tentative->isByUser($user)) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $tentative->update($request->validated());

        return response()->json([
            'message' => 'Tentative mise à jour avec succès.',
            'tentative' => $tentative,
        ]);
    }

    /**
     * Tentatives d'un test (avec nom et matricule).
     */
    public function getByTest(int $id_test): AnonymousResourceCollection
    {
        $tentatives = Tentative::with('utilisateur:id_utilisateur,nom,matricule')
            ->where('id_test', $id_test)
            ->get();

        return TentativeResource::collection($tentatives);
    }

    /**
     * Détail d'une tentative (avec réponses, questions, test, groupe).
     */
    public function getTentativeById(int $id_tentative): JsonResponse
    {
        $tentative = Tentative::with([
            'utilisateur',
            'test' => fn($q) => $q->with('group'),
            'reponses' => fn($q) => $q->with('question'),
        ])->findOrFail($id_tentative);

        return response()->json($tentative);
    }
}
