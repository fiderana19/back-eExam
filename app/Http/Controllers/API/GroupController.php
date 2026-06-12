<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreGroupRequest;
use App\Http\Requests\API\UpdateGroupRequest;
use App\Models\Group;
use Illuminate\Http\JsonResponse;

class GroupController extends Controller
{
    /**
     * Liste des groupes visibles (hors ADMIN).
     */
    public function index(): JsonResponse
    {
        $groups = Group::visible()->get();
        return response()->json($groups);
    }

    /**
     * Détail d'un groupe.
     */
    public function show(Group $group): JsonResponse
    {
        return response()->json($group);
    }

    /**
     * Créer un groupe.
     */
    public function store(StoreGroupRequest $request): JsonResponse
    {
        $group = Group::create($request->validated());

        return response()->json([
            'message' => 'Groupe créé avec succès',
            'group' => $group,
        ], 201);
    }

    /**
     * Modifier un groupe (admin uniquement — les enseignants n'ont pas accès).
     */
    public function update(UpdateGroupRequest $request, int $id): JsonResponse
    {
        $group = Group::find($id);

        if (!$group) {
            return response()->json(['message' => 'Groupe introuvable'], 404);
        }

        $group->update($request->validated());

        return response()->json([
            'message' => 'Groupe modifié avec succès',
            'group' => $group,
        ]);
    }

    /**
     * Supprimer un groupe (admin uniquement).
     */
    public function destroy(int $id): JsonResponse
    {
        $group = Group::find($id);

        if (!$group) {
            return response()->json(['message' => 'Groupe introuvable'], 404);
        }

        $group->delete();

        return response()->json(['message' => 'Groupe supprimé avec succès']);
    }
}
