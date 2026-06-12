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

// ════════════════════════════════════════════════════════════════
// AUTHENTIFICATION
// Prefix: /api/auth
// ════════════════════════════════════════════════════════════════
Route::prefix('auth')->controller(AuthController::class)->group(function () {

    // Routes publiques
    Route::post('register', 'register')                ->name('auth.register');
    Route::post('login', 'login')                      ->name('auth.login');

    // Routes protégées — utilisateur connecté
    Route::middleware('auth:api')->group(function () {
        Route::get('profile', 'profile')               ->name('auth.profile');
        Route::post('logout', 'logout')                ->name('auth.logout');
        Route::post('refresh', 'refresh')              ->name('auth.refresh');
        Route::get('{user}', 'show')                   ->name('auth.show');
    });
});

// ════════════════════════════════════════════════════════════════
// ADMINISTRATION
// Prefix: /api/admin — Middleware: auth:api, role:admin
// ════════════════════════════════════════════════════════════════
Route::middleware(['auth:api', 'role:admin'])->prefix('admin')
    ->controller(AdminController::class)->group(function () {

    Route::get('users', 'allUsers')                    ->name('admin.users.all');
    Route::get('users/pending', 'pendingUsers')        ->name('admin.users.pending');
    Route::post('users/approve/{id_utilisateur}', 'approveUser')
        ->name('admin.users.approve');
    Route::post('users/block/{id_utilisateur}', 'blockUser')
        ->name('admin.users.block');
});

// ════════════════════════════════════════════════════════════════
// TESTS
// Prefix: /api/tests — Middleware: auth:api
// ════════════════════════════════════════════════════════════════
Route::middleware(['auth:api'])->prefix('tests')
    ->controller(TestController::class)->group(function () {

    // Consultation — tous les rôles connectés
    Route::get('{test}', 'show')                       ->name('tests.show');
    Route::get('groupe/{id_groupe}', 'getByGroup')     ->name('tests.byGroup');
    Route::get('user/{id_utilisateur}', 'getByUser')   ->name('tests.byUser');
    Route::get('/all_corrected', 'getCorrectedTest')   ->name('tests.allCorrected');
    Route::get('/all_corrected/admin', 'getCorrectedTestByAdmin')
        ->name('tests.allCorrectedAdmin');
    Route::get('/results/{id_test}', 'getTestsWithStats')
        ->name('tests.results');
    Route::get('/need_correction/{id_utilisateur}', 'getTestsWithUnnotedAttempts')
        ->name('tests.needCorrection');
    Route::put('/finish/{test}', 'finish')              ->name('tests.finish');

    // Gestion — enseignant + admin uniquement
    Route::middleware('role:enseignant,admin')->group(function () {
        Route::post('/', 'store')                       ->name('tests.store');
        Route::put('{id}', 'update')                    ->name('tests.update');
        Route::put('/launch/{id}', 'updateStartTime')   ->name('tests.updateStartTime');
        Route::delete('{id}', 'destroy')                ->name('tests.destroy');
    });
});

// ════════════════════════════════════════════════════════════════
// QUESTIONS
// Prefix: /api/questions — Middleware: auth:api
// ════════════════════════════════════════════════════════════════
Route::middleware(['auth:api'])->prefix('questions')
    ->controller(QuestionController::class)->group(function () {

    // Consultation — tous les rôles connectés
    Route::get('/{question}', 'show')                  ->name('questions.show');
    Route::get('/test/{id_test}', 'getByTest')         ->name('questions.byTest');
    Route::get('/test/random/{id_test}', 'randomByTest')
        ->name('questions.randomByTest');

    // Gestion — enseignant + admin uniquement
    Route::middleware('role:enseignant,admin')->group(function () {
        Route::post('/', 'store')                       ->name('questions.store');
        Route::put('/{id}', 'update')                   ->name('questions.update');
        Route::delete('/{id}', 'destroy')               ->name('questions.destroy');
    });
});

// ════════════════════════════════════════════════════════════════
// OPTIONS
// Prefix: /api/options — Middleware: auth:api
// ════════════════════════════════════════════════════════════════
Route::middleware(['auth:api'])->prefix('options')
    ->controller(OptionController::class)->group(function () {

    // Consultation — tous les rôles connectés
    Route::get('/question/{id_question}', 'getByQuestion')
        ->name('options.byQuestion');

    // Gestion — enseignant + admin uniquement
    Route::middleware('role:enseignant,admin')->group(function () {
        Route::post('/', 'store')                       ->name('options.store');
        Route::delete('/{id}', 'destroy')               ->name('options.destroy');
    });
});

// ════════════════════════════════════════════════════════════════
// TENTATIVES
// Prefix: /api/tentatives — Middleware: auth:api
// ════════════════════════════════════════════════════════════════
Route::middleware('auth:api')->prefix('tentatives')
    ->controller(TentativeController::class)->group(function () {

    Route::post('/', 'store')                            ->name('tentatives.store');
    Route::put('/{id_tentative}', 'update')              ->name('tentatives.update');
    Route::get('/test/{id_test}', 'getByTest')           ->name('tentatives.byTest');
    Route::get('/responses/{id_test}', 'getTentativeById')
        ->name('tentatives.responses');
});

// ════════════════════════════════════════════════════════════════
// GROUPES
// Prefix: /api/groupes
// ════════════════════════════════════════════════════════════════
Route::prefix('groupes')->controller(GroupController::class)->group(function () {

    // Consultation publique
    Route::get('/', 'index')                             ->name('groupes.index');
    Route::get('/{group}', 'show')                       ->name('groupes.show');

    // Gestion — utilisateur connecté
    Route::middleware('auth:api')->group(function () {
        Route::post('/', 'store')                        ->name('groupes.store');
        Route::put('/{id}', 'update')                    ->name('groupes.update');
        Route::delete('/{id}', 'destroy')                ->name('groupes.destroy');
    });
});

// ════════════════════════════════════════════════════════════════
// REPONSES
// Prefix: /api/reponses — Middleware: auth:api
// ════════════════════════════════════════════════════════════════
Route::middleware('auth:api')->prefix('reponses')
    ->controller(ReponseController::class)->group(function () {

    Route::post('/', 'store')                            ->name('reponses.store');
    Route::put('/{id}/texte', 'updateTexte')             ->name('reponses.updateTexte');
    Route::put('/corriger/{id}/', 'corrigerReponse')     ->name('reponses.corriger');
    Route::get('/non-corrigees', 'getNonCorrigees')      ->name('reponses.nonCorrigees');
    Route::get('/test/{id_test}', 'getByTest')           ->name('reponses.byTest');
    Route::get('/{id}', 'show')                          ->name('reponses.show');
});

// ════════════════════════════════════════════════════════════════
// ANNONCES
// Prefix: /api/annonces — Middleware: auth:api
// ════════════════════════════════════════════════════════════════
Route::middleware('auth:api')->prefix('annonces')
    ->controller(AnnonceController::class)->group(function () {

    Route::post('/', 'store')                            ->name('annonces.store');
    Route::get('/{annonce}', 'show')                     ->name('annonces.show');
    Route::get('/groupe/{id_groupe}', 'getByGroupe')     ->name('annonces.byGroup');
    Route::get('/groupe/{id_groupe}/dernieres', 'lastByGroupe')
        ->name('annonces.lastByGroup');
    Route::get('/utilisateur/{id_utilisateur}', 'lastByUser')
        ->name('annonces.lastByUser');
    Route::put('/{id}', 'update')                        ->name('annonces.update');
    Route::delete('/{id}', 'destroy')                    ->name('annonces.destroy');
});

// ════════════════════════════════════════════════════════════════
// RESULTATS
// Prefix: /api/resultats — Middleware: auth:api
// ════════════════════════════════════════════════════════════════
Route::middleware('auth:api')->prefix('resultats')
    ->controller(ResultatController::class)->group(function () {

    // Téléchargement — tous les rôles connectés
    Route::get('/download/{id}', 'download')             ->name('resultats.download');

    // Consultation — résultats d'un groupe (enseignant + admin)
    Route::get('/groupe/{id_groupe}', 'getByGroupe')     ->name('resultats.byGroup');

    // Création — enseignant + admin
    Route::post('/', 'store')                            ->name('resultats.store')
        ->middleware('role:enseignant,admin');

    // Admin uniquement
    Route::get('/', 'getAll')                            ->name('resultats.all')
        ->middleware('role:admin');
    Route::delete('/{id}', 'destroy')                    ->name('resultats.destroy')
        ->middleware('role:admin');
});
