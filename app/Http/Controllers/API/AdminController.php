<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Utilisateur;

class AdminController extends Controller
{

    public function pendingUsers()
    {
        $utilisateurs = Utilisateur::where('est_valider', false)
            ->whereIn('role', ['etudiant', 'enseignant'])
            ->get();

        return response()->json([
            'message' => 'Liste des utilisateurs en attente de validation.',
            'data' => $utilisateurs
        ]);
    }

    public function approveUser($id)
    {
        $utilisateur = Utilisateur::findOrFail($id);

        if ($utilisateur->est_valider) {
            return response()->json(['message' => 'Cet utilisateur est déjà validé.'], 400);
        }

        $utilisateur->est_valider = true;
        $utilisateur->save();

        return response()->json([
            'message' => 'Utilisateur validé avec succès.',
            'utilisateur' => $utilisateur
        ]);
    }

    public function blockUser($id)
    {
        $utilisateur = Utilisateur::findOrFail($id);

        if (!$utilisateur->est_valider) {
            return response()->json(['message' => 'Cet utilisateur est déjà bloqué.'], 400);
        }

        $utilisateur->est_valider = false;
        $utilisateur->save();

        return response()->json([
            'message' => 'Utilisateur bloqué avec succès.',
            'utilisateur' => $utilisateur
        ]);
    }

    public function allUsers()
    {
        $utilisateurs = Utilisateur::where('role', '!=', 'admin')->get();

        return response()->json([
            'message' => 'Liste de tous les utilisateurs.',
            'data' => $utilisateurs
        ]);
    }
}
