<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    /**
     * Afficher tous les groupes
     */
    public function index()
    {
        $groups = Group::get();
        return response()->json($groups);
    }

    /**
     * Afficher un groupe par ID
     */
    public function show($id)
    {
        $group = Group::find($id);

        if (!$group) {
            return response()->json(['message' => 'Groupe introuvable'], 404);
        }

        return response()->json($group);
    }

    /**
     * Créer un nouveau groupe (enseignant ou admin)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom_groupe' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $group = Group::create([
            'nom_groupe' => $request->nom_groupe,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Groupe créé avec succès',
            'group' => $group
        ], 201);
    }

    /**
     * Modifier un groupe (enseignant ou admin)
     */
    public function update(Request $request, $id)
    {
        $group = Group::find($id);

        if (!$group) {
            return response()->json(['message' => 'Groupe introuvable'], 404);
        }

        // Vérifie les permissions
        if (Auth::user()->role === 'enseignant') {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $group->update([
            'nom_groupe' => $request->nom_groupe ?? $group->nom_groupe,
            'description' => $request->description ?? $group->description,
        ]);

        return response()->json(['message' => 'Groupe modifié avec succès', 'group' => $group]);
    }

    /**
     * Supprimer un groupe (enseignant ou admin)
     */
    public function destroy($id)
    {
        $group = Group::find($id);

        if (!$group) {
            return response()->json(['message' => 'Groupe introuvable'], 404);
        }

        // Vérifie les permissions
        if (Auth::user()->role === 'enseignant') {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $group->delete();

        return response()->json(['message' => 'Groupe supprimé avec succès']);
    }
}
