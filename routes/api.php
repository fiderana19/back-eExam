<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\TestController;
use App\Http\Controllers\API\QuestionController;
use App\Http\Controllers\API\OptionController;
use App\Http\Controllers\API\TentativeController;
use App\Http\Controllers\API\ReponseController;
use App\Http\Controllers\API\AnnonceController;
use App\Http\Controllers\API\ResultatController;
use App\Http\Controllers\API\GroupController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('{id}', [AuthController::class, 'getById']);
    });
});

Route::middleware(['auth:api', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('users/pending', [AdminController::class, 'pendingUsers']);
    Route::post('users/approve/{id_utilisateur}', [AdminController::class, 'approveUser']);
    Route::post('users/block/{id_utilisateur}', [AdminController::class, 'blockUser']);
    Route::get('users', [AdminController::class, 'allUsers']);
});

Route::middleware(['auth:api'])->prefix('tests')->group(function () {

    // Récupérer les tests en cours d’un groupe
    Route::get('groupe/{id_groupe}', [TestController::class, 'getByGroup']);

    // Récupérer un test par son id
    Route::get('{id}', [TestController::class, 'show']);

    // Récupérer les tests créés par un utilisateur
    Route::get('user/{id_utilisateur}', [TestController::class, 'getByUser']);

    // Récupérer les tests avec des tentatives non notées
    Route::get('tentatives/non-notees', [TestController::class, 'getTestsWithUnnotedAttempts']);

    // Récupérer les tests complètement notés + statistiques
    Route::get('tentatives/statistiques', [TestController::class, 'getTestsWithStats']);

    // Récupérer les tests complètement notés par utilisateur
    Route::get('tentatives/statistiques/{id_utilisateur}', [TestController::class, 'getTestsWithStats']);

    // Actions protégées (enseignant + admin)
    Route::middleware('role:enseignant,admin')->group(function () {
        // Créer un test
        Route::post('/', [TestController::class, 'store']);

        // Modifier un test
        Route::put('{id}', [TestController::class, 'update']);

        // Supprimer un test
        Route::delete('{id}', [TestController::class, 'destroy']);

        // Modifier l’heure de déclenchement d’un test
        Route::put('{id}/start-time', [TestController::class, 'updateStartTime']);
    });
});

Route::middleware(['auth:api'])->prefix('questions')->group(function () {
    // Lecture
    Route::get('/test/{id_test}', [QuestionController::class, 'getByTest']);
    Route::get('/test/{id_test}/random', [QuestionController::class, 'randomByTest']);

    // Écriture
    Route::middleware('role:enseignant,admin')->group(function () {
        Route::post('/', [QuestionController::class, 'store']);
        Route::put('/{id}', [QuestionController::class, 'update']);
        Route::delete('/{id}', [QuestionController::class, 'destroy']); 
    });
});

Route::middleware(['auth:api'])->prefix('options')->group(function () {
    Route::get('/question/{id_question}', [OptionController::class, 'getByQuestion']);

    Route::middleware('role:enseignant,admin')->group(function () {
        Route::post('/', [OptionController::class, 'store']);       
        Route::delete('/{id}', [OptionController::class, 'destroy']);  
    });
});

Route::middleware('auth:api')->group(function () {
    Route::post('/tentatives', [TentativeController::class, 'store']);
    Route::put('/tentatives/{id_tentative}', [TentativeController::class, 'update']);
    Route::get('/tentatives/test/{id_test}', [TentativeController::class, 'getByTest']);
});

Route::get('/groupes', [GroupController::class, 'index']);
Route::get('/groupes/{group}', [GroupController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::post('/groupes', [GroupController::class, 'store']);
    Route::put('/groupes/{id}', [GroupController::class, 'update']);
    Route::delete('/groupes/{id}', [GroupController::class, 'destroy']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/reponses', [ReponseController::class, 'store']);
    Route::put('/reponses/{id}/texte', [ReponseController::class, 'updateTexte']);
    Route::put('/reponses/{id}/corriger', [ReponseController::class, 'corrigerReponse']);
    Route::get('/reponses/test/{id_test}', [ReponseController::class, 'getByTest']);
    Route::get('/reponses/{id}', [ReponseController::class, 'show']);
    Route::get('/reponses-non-corrigees', [ReponseController::class, 'getNonCorrigees']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/annonces', [AnnonceController::class, 'store']);
    Route::get('/annonces/groupe/{id_groupe}', [AnnonceController::class, 'getByGroupe']);
    Route::get('/annonces/{id}', [AnnonceController::class, 'show']);
    Route::get('/annonces/groupe/{id_groupe}/dernieres', [AnnonceController::class, 'lastByGroupe']);
    Route::get('/annonces/utilisateur/{id_utilisateur}/dernieres', [AnnonceController::class, 'lastByUser']);
    Route::put('/annonces/{id}', [AnnonceController::class, 'update']);
    Route::delete('/annonces/{id}', [AnnonceController::class, 'destroy']);
});

Route::middleware('auth:api')->prefix('resultats')->group(function () {

    // Récupérer tous les résultats (admin uniquement)
    Route::get('/', [ResultatController::class, 'index'])->middleware('role:admin');

    // Récupérer les résultats d’un groupe (enseignant + admin)
    Route::get('/groupe/{id_groupe}', [ResultatController::class, 'getByGroupe'])
        ->middleware('role:enseignant,admin');

    // Créer un résultat (enseignant + admin)
    Route::post('/', [ResultatController::class, 'store'])
        ->middleware('role:enseignant,admin');

    // Supprimer un résultat (admin uniquement)
    Route::delete('/{id}', [ResultatController::class, 'destroy'])
        ->middleware('role:admin');

    // Télécharger un fichier résultat (tous les rôles)
    Route::get('/{id}/download', [ResultatController::class, 'download']);
});