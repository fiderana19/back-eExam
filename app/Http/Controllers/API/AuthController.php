<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Utilisateur;
use App\Models\Group;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
       $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'email' => 'required|string|email|unique:utilisateurs,email',
            'matricule' => 'required|string|min:7',
            'password' => 'required|string|min:8',
            'id_groupe' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $validated['password'] = Hash::make($validated['password']);
        $validated['est_valider'] = false;
        $group = Group::findOrFail($validated['id_groupe']);
        if($group['nom_groupe'] === "ENSEIGNANT") {
            $validated['role'] = 'enseignant';
        } else {
            $validated['role'] = 'etudiant';
        }

        $utilisateur = Utilisateur::create($validated);

        return response()->json([
            'message' => 'Inscription réussie. En attente de validation par un administrateur.',
            'utilisateur' => $utilisateur
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Identifiants invalides.'], 401);
        }

        $user = auth()->user();

        if (!$user->isApproved()) {
            return response()->json(['message' => 'En attente de validation.'], 403);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'utilisateur' => $user
        ]);
    }

    public function show(Utilisateur $user)
    {
        return response()->json($user);
    }

    public function profile()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    public function refresh()
    {
        return response()->json([
            'access_token' => auth()->refresh(),
            'token_type' => 'bearer'
        ]);
    }
}