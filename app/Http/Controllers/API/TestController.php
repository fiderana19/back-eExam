<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreTestRequest;
use App\Http\Requests\API\UpdateTestRequest;
use App\Http\Resources\API\TestResource;
use App\Models\Test;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TestController extends Controller
{
    /**
     * Tests "En cours" d'un groupe.
     */
    public function getByGroup(int $id_groupe): AnonymousResourceCollection
    {
        $tests = Test::with('group')
            ->where('id_groupe', $id_groupe)
            ->pending()
            ->get();

        return TestResource::collection($tests);
    }

    /**
     * Détail d'un test.
     */
    public function show(Test $test): TestResource
    {
        $test->load('group', 'createur');
        return new TestResource($test);
    }

    /**
     * Tests créés par un utilisateur.
     */
    public function getByUser(int $id_utilisateur): AnonymousResourceCollection
    {
        $user = auth()->user();

        if (!$user->isAdmin() && (int) $user->id_utilisateur !== $id_utilisateur) {
            abort(403, 'Accès refusé');
        }

        $tests = Test::with('group')
            ->where('id_utilisateur', $id_utilisateur)
            ->get();

        return TestResource::collection($tests);
    }

    /**
     * Tests terminés avec tentatives non notées.
     */
    public function getTestsWithUnnotedAttempts(): JsonResponse
    {
        $user = auth()->user();
        $userId = $user->isAdmin() ? null : $user->id_utilisateur;

        $tests = Test::withUnnotedAttempts($userId)->get();

        return response()->json($tests);
    }

    /**
     * Tests dont toutes les tentatives sont notées, avec statistiques.
     */
    public function getTestsWithStats(int $id_test): JsonResponse
    {
        $test = Test::with([
            'tentatives' => fn($q) => $q->noted()->with('utilisateur'),
            'group',
        ])->findOrFail($id_test);

        return response()->json([$test->getStats()]);
    }

    /**
     * Créer un test.
     */
    public function store(StoreTestRequest $request): JsonResponse
    {
        $test = Test::create([
            ...$request->validated(),
            'id_utilisateur' => $request->user()->id_utilisateur,
        ]);

        return response()->json([
            'message' => 'Test créé avec succès',
            'test' => new TestResource($test),
        ], 201);
    }

    /**
     * Modifier un test.
     */
    public function update(UpdateTestRequest $request, Test $test): JsonResponse
    {
        if (!$test->isEditableBy($request->user())) {
            return response()->json(['message' => 'Vous ne pouvez modifier que vos propres tests'], 403);
        }

        $test->update($request->validated());

        return response()->json([
            'message' => 'Test modifié avec succès',
            'test' => new TestResource($test),
        ]);
    }

    /**
     * Supprimer un test.
     */
    public function destroy(Test $test): JsonResponse
    {
        if (!$test->isEditableBy(auth()->user())) {
            return response()->json(['message' => 'Vous ne pouvez supprimer que vos propres tests'], 403);
        }

        $test->delete();

        return response()->json(['message' => 'Test supprimé avec succès']);
    }

    /**
     * Démarrer un test (passer en statut "En cours").
     */
    public function updateStartTime(Test $test): JsonResponse
    {
        if (auth()->user()->isEtudiant()) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $test->launch();

        return response()->json(['message' => 'Heure de déclenchement mise à jour'], 200);
    }

    /**
     * Terminer un test (passer en statut "Terminé").
     */
    public function finish(Test $test): JsonResponse
    {
        if (auth()->user()->isEtudiant()) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $test->finish();

        return response()->json(['message' => 'Test terminé'], 200);
    }

    /**
     * Tests corrigés (admin) — tous les tests terminés et notés.
     */
    public function getCorrectedTestByAdmin(): JsonResponse
    {
        $tests = Test::corrected()->with('group')->get();
        return response()->json($tests);
    }

    /**
     * Tests corrigés (enseignant) — propres tests terminés et notés.
     */
    public function getCorrectedTest(): JsonResponse
    {
        $user = auth()->user();
        $tests = Test::corrected()
            ->where('id_utilisateur', $user->id_utilisateur)
            ->with('group')
            ->get();

        return response()->json($tests);
    }
}
