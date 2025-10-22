<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Test;
use App\Models\Tentative;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestController extends Controller
{
    /**
     * Récupérer les tests "en cours" par id du groupe
     */
    public function getByGroup($id_groupe)
    {
        $tests = Test::where('id_groupe', $id_groupe)
            ->where('status', 'En cours')
            ->get();

        return response()->json($tests);
    }

    /**
     * Récupérer un test par son id
     */
    public function show($id)
    {
        $test = Test::with(['groupe', 'utilisateur'])->find($id);

        if (!$test) {
            return response()->json(['message' => 'Test introuvable'], 404);
        }

        return response()->json($test);
    }

    /**
     * Récupérer les tests créés par un utilisateur
     */
    public function getByUser($id_utilisateur)
    {
        $user = auth()->user();

        // Un enseignant ne peut voir que ses propres tests
        if ($user->role === 'enseignant' && $user->id_utilisateur != $id_utilisateur) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        // Un étudiant ne peut voir que ses propres tests (par tentative)
        if ($user->role === 'etudiant' && $user->id_utilisateur != $id_utilisateur) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $tests = Test::where('id_utilisateur', $id_utilisateur)->get();
        return response()->json($tests);
    }

    /**
     * Tests avec tentatives non notées
     */
    public function getTestsWithUnnotedAttempts()
    {
        $user = auth()->user();

        $tests = Test::whereHas('tentatives', function ($q) {
            $q->where('est_noter', false);
        })
        ->when($user->role === 'enseignant', function ($q) use ($user) {
            $q->where('id_utilisateur', $user->id_utilisateur);
        })
        ->get();

        return response()->json($tests);
    }

    /**
     * Tests dont toutes les tentatives sont notées + stats
     */
    public function getTestsWithStats()
    {
        $user = auth()->user();

        $tests = Test::whereDoesntHave('tentatives', function ($q) {
            $q->where('est_noter', false);
        })
        ->when($user->role === 'enseignant', function ($q) use ($user) {
            $q->where('id_utilisateur', $user->id_utilisateur);
        })
        ->with('tentatives')
        ->get();

        $data = $tests->map(function ($test) {
            $tentatives = $test->tentatives;
            $total = $tentatives->count();
            $supMoyenne = $tentatives->where('note_obtenue', '>=', $test->note_max / 2)->count();
            $infMoyenne = $tentatives->where('note_obtenue', '<', $test->note_max / 2)->count();

            return [
                'test' => $test,
                'total_tentatives' => $total,
                'superieur_ou_egal_moyenne' => $supMoyenne,
                'inferieur_moyenne' => $infMoyenne,
            ];
        });

        return response()->json($data);
    }

    /**
     * Supprimer un test
     */
    public function destroy($id)
    {
        $test = Test::find($id);
        $user = auth()->user();

        if (!$test) {
            return response()->json(['message' => 'Test introuvable'], 404);
        }

        if ($user->role === 'etudiant') {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        if ($user->role === 'enseignant' && $test->id_utilisateur != $user->id_utilisateur) {
            return response()->json(['message' => 'Vous ne pouvez supprimer que vos propres tests'], 403);
        }

        $test->delete();
        return response()->json(['message' => 'Test supprimé avec succès']);
    }

    /**
     * Modifier l'heure de déclenchement
     */
    public function updateStartTime($id)
    {
        $test = Test::find($id);
        $user = auth()->user();

        if (!$test) {
            return response()->json(['message' => 'Test introuvable'], 404);
        }

        if ($user->role === 'etudiant') {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        if ($user->role === 'enseignant' && $test->id_utilisateur != $user->id_utilisateur) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $test->date_declenchement = Carbon::now();
        $test->save();

        return response()->json(['message' => 'Heure de déclenchement mise à jour']);
    }

    /**
     * Créer un test
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'etudiant') {
            return response()->json(['message' => 'Les étudiants ne peuvent pas créer de test'], 403);
        }

        $validated = $request->validate([
            'id_groupe' => 'required|exists:groupes,id_groupe',
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duree_minutes' => 'required|integer',
            'max_questions' => 'required|integer',
            'note_max' => 'required|numeric',
            'date_declenchement' => 'nullable|date',
            'status' => 'required|string',
        ]);

        $validated['id_utilisateur'] = $user->id_utilisateur;

        $test = Test::create($validated);

        return response()->json(['message' => 'Test créé avec succès', 'test' => $test], 201);
    }

    /**
     * Modifier un test
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $test = Test::find($id);

        if (!$test) {
            return response()->json(['message' => 'Test introuvable'], 404);
        }

        if ($user->role === 'etudiant') {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        if ($user->role === 'enseignant' && $test->id_utilisateur != $user->id_utilisateur) {
            return response()->json(['message' => 'Vous ne pouvez modifier que vos propres tests'], 403);
        }

        $validated = $request->validate([
            'titre' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'duree_minutes' => 'sometimes|integer',
            'max_questions' => 'sometimes|integer',
            'note_max' => 'sometimes|numeric',
            'status' => 'sometimes|string',
        ]);

        $test->update($validated);

        return response()->json(['message' => 'Test modifié avec succès', 'test' => $test]);
    }
}