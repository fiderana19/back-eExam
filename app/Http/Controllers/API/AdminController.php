<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * Liste des utilisateurs en attente de validation.
     */
    public function pendingUsers(): JsonResponse
    {
        $utilisateurs = Utilisateur::pending()->get();

        return response()->json([
            'message' => 'Liste des utilisateurs en attente de validation.',
            'data' => $utilisateurs,
        ]);
    }

    /**
     * Approuver un utilisateur.
     */
    public function approveUser(int $id): JsonResponse
    {
        $utilisateur = Utilisateur::findOrFail($id);

        if ($utilisateur->isApproved()) {
            return response()->json(['message' => 'Cet utilisateur est déjà validé.'], 400);
        }

        $utilisateur->approve();

        return response()->json([
            'message' => 'Utilisateur validé avec succès.',
            'utilisateur' => $utilisateur,
        ]);
    }

    /**
     * Bloquer (désactiver) un utilisateur.
     */
    public function blockUser(int $id): JsonResponse
    {
        $utilisateur = Utilisateur::findOrFail($id);

        if (!$utilisateur->isApproved()) {
            return response()->json(['message' => 'Cet utilisateur est déjà désactivé.'], 400);
        }

        $utilisateur->block();

        return response()->json([
            'message' => 'Utilisateur désactivé avec succès.',
            'utilisateur' => $utilisateur,
        ]);
    }

    /**
     * Liste de tous les utilisateurs (hors admin).
     */
    public function allUsers(): JsonResponse
    {
        $utilisateurs = Utilisateur::nonAdmin()->get();

        return response()->json([
            'message' => 'Liste de tous les utilisateurs.',
            'data' => $utilisateurs,
        ]);
    }
}
