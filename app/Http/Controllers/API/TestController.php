<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Test;
use App\Models\Tentative;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class TestController extends Controller
{
    /**
     * Récupérer les tests "en cours" par id du groupe
     */
    public function getByGroup($id_groupe)
    {
        $tests = Test::with('group')
            ->where('id_groupe', $id_groupe)
            ->where('status', 'En cours')
            ->get();

        $data = $tests->map(function ($test) {
            return [
                'id_test' => $test->id_test,
                'nom_groupe' => $test->group->nom_groupe ?? 'Aucun groupe',
                'id_utilisateur' => $test->id_utilisateur,
                'id_groupe' => $test->id_groupe,
                'titre' => $test->titre,
                'description' => $test->description,
                'duree_minutes' => $test->duree_minutes,
                'max_questions' => $test->max_questions,
                'note_max' => $test->note_max,
                'date_declechement' => $test->date_declechement,
                'status' => $test->status,
                ];
        });
        
        return response()->json($data);
    }

    /**
     * Récupérer un test par son id
     */
    public function show(Test $test)
    {
        if (!$test) {
            return response()->json(['message' => 'Test introuvable'], 404);
        }
    
        return response()->json([
            'id_test' => $test->id_test,
            'nom_groupe' => $test->group->nom_groupe ?? 'Aucun groupe',
            'nom' => $test->createur->nom ?? 'Aucun utilisateur',
            'id_utilisateur' => $test->id_utilisateur,
            'id_groupe' => $test->id_groupe,
            'titre' => $test->titre,
            'description' => $test->description,
            'duree_minutes' => $test->duree_minutes,
            'max_questions' => $test->max_questions,
            'note_max' => $test->note_max,
            'date_declechement' => $test->date_declechement,
            'status' => $test->status,
        ]);
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

        $tests = Test::with('group')->where('id_utilisateur', $id_utilisateur)->get();

        $data = $tests->map(function ($test) {
            return [
                'id_test' => $test->id_test,
                'nom_groupe' => $test->group->nom_groupe ?? 'Aucun groupe',
                'id_utilisateur' => $test->id_utilisateur,
                'id_groupe' => $test->id_groupe,
                'titre' => $test->titre,
                'description' => $test->description,
                'duree_minutes' => $test->duree_minutes,
                'max_questions' => $test->max_questions,
                'note_max' => $test->note_max,
                'date_declechement' => $test->date_declechement,
                'status' => $test->status,
                ];
        });
        
        return response()->json($data);
    }

    /**
     * Tests avec tentatives non notées
     */
    public function getTestsWithUnnotedAttempts($id_utilisateur)
    {
        $user = auth()->user();

        $tests = Test::whereHas('tentatives', function ($q) {
            $q->where('est_noter', false);
        })
        ->when($user->role === 'enseignant', function ($q) use ($user) {
            $q->where('id_utilisateur', $user->id_utilisateur);
        })
        ->where('status', 'Terminé')
        ->get();
        
        return response()->json($tests);
    }

    /**
     * Tests dont toutes les tentatives sont notées + stats
     */
    public function getTestsWithStats($id_test)
    {
        $user = auth()->user();

        $tests = Test::where('id_test', $id_test)
        ->whereDoesntHave('tentatives', function ($q) {
            $q->where('est_noter', false);
        })
        ->with([
            'tentatives' => function ($q) {
                $q->where('est_noter', true)
                  ->with('utilisateur'); 
            },
            'group'
        ])
        ->get();

        $data = $tests->map(function ($test) {
            $tentatives = $test->tentatives;
            $total = $tentatives->count();
            $supMoyenne = $tentatives->where('note_obtenue', '>=', $test->note_max / 2)->count();
            $infMoyenne = $tentatives->where('note_obtenue', '<', $test->note_max / 2)->count();

            return [
                'test' => $test,
                'total' => $total,
                'sup' => $supMoyenne,
                'sous' => $infMoyenne,
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

        $test->status = 'En cours';
        $test->date_declechement = Carbon::now();
        $test->save();

        return response()->json(['message' => 'Heure de déclenchement mise à jour'], 200);
    }

    /**
     * Terminer le test
     */
    public function finish($id)
    {
        $test = Test::find($id);
        $user = auth()->user();

        if (!$test) {
            return response()->json(['message' => 'Test introuvable'], 404);
        }

        if ($user->role === 'etudiant') {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $test->status = 'Terminé';
        $test->save();

        return response()->json(['message' => 'Test terminé'], 200);
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
        ]);

        $test->update($validated);

        return response()->json(['message' => 'Test modifié avec succès', 'test' => $test]);
    }

    public function getCorrectedTestByAdmin()
    {
        $tests = Test::where('status', 'Terminé')
        ->has('tentatives') 
        ->whereDoesntHave('tentatives', function ($queryTentative) {
            $queryTentative->where('est_noter', false);
        })
        ->with([
            'group', 
        ])
        ->get();
        
        return response()->json($tests);
    }

    public function getCorrectedTest()
    {
        $user = auth()->user();

        $tests = Test::where('id_utilisateur', $user->id_utilisateur)
        ->where('status', 'Terminé')
        ->has('tentatives') 
        ->whereDoesntHave('tentatives', function ($queryTentative) {
            $queryTentative->where('est_noter', false);
        })
        ->with([
            'group', 
        ])
        ->get();
        
        return response()->json($tests);
    }
}