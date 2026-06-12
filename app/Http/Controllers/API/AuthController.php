<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\RegisterRequest;
use App\Models\Utilisateur;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Inscription d'un nouvel utilisateur.
     *
     * Crée un compte avec le rôle déterminé automatiquement
     * à partir du groupe choisi. Le compte est en attente de validation.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['est_valider'] = false;
        $data['role'] = Utilisateur::determineRoleFromGroup($data['id_groupe']);

        $utilisateur = Utilisateur::create($data);

        return response()->json([
            'message' => 'Inscription réussie. En attente de validation par un administrateur.',
            'utilisateur' => $utilisateur,
        ], 201);
    }

    /**
     * Connexion d'un utilisateur.
     *
     * Vérifie les identifiants et que le compte est approuvé.
     */
    public function login(): JsonResponse
    {
        $credentials = request()->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }

        $user = auth()->user();

        if (!$user->isApproved()) {
            return response()->json(['message' => 'Votre compte est toujours en attente de validation.'], 403);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'utilisateur' => $user,
        ]);
    }

    /**
     * Afficher un utilisateur par son ID (route model binding).
     */
    public function show(Utilisateur $user): JsonResponse
    {
        return response()->json([
            'id_utilisateur' => $user->id_utilisateur,
            'nom' => $user->nom,
            'email' => $user->email,
            'matricule' => $user->matricule,
            'role' => $user->role,
            'est_valider' => $user->est_valider,
            'id_groupe' => $user->id_groupe,
            'nom_groupe' => $user->groupe?->nom_groupe ?? 'Aucun groupe',
        ]);
    }

    /**
     * Profil de l'utilisateur connecté.
     */
    public function profile(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    /**
     * Déconnexion (invalidation du token).
     */
    public function logout(): JsonResponse
    {
        auth()->logout();
        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    /**
     * Rafraîchir le token JWT.
     */
    public function refresh(): JsonResponse
    {
        return response()->json([
            'access_token' => auth()->refresh(),
            'token_type' => 'bearer',
        ]);
    }
}
