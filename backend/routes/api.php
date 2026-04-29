<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\NotificationController;



Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

    // Super Admin
    Route::prefix('admin')->middleware('role:superadmin')->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'index']);

        // Coachs
        Route::apiResource('coachs', SuperAdminController::class)->except(['index', 'show']);

        Route::post('/coachs', [SuperAdminController::class, 'storeCoach']);
        Route::put('/coachs/{coach}', [SuperAdminController::class, 'updateCoach']);
        Route::delete('/coachs/{coach}', [SuperAdminController::class, 'destroyCoach']);

        // Parents
        Route::post('/parents', [SuperAdminController::class, 'storeParent']);
        Route::put('/parents/{parent}', [SuperAdminController::class, 'updateParent']);
        Route::delete('/parents/{parent}', [SuperAdminController::class, 'destroyParent']);

        // Categories
        Route::post('/categories', [SuperAdminController::class, 'storeCategorie']);
        Route::put('/categories/{categorie}', [SuperAdminController::class, 'updateCategorie']);
        Route::delete('/categories/{categorie}', [SuperAdminController::class, 'destroyCategorie']);

        // Groupes
        Route::post('/groupes', [SuperAdminController::class, 'storeGroupe']);
        Route::put('/groupes/{groupe}', [SuperAdminController::class, 'updateGroupe']);
        Route::delete('/groupes/{groupe}', [SuperAdminController::class, 'destroyGroupe']);
        Route::post('/groupes/{groupe}/assign-coach', [SuperAdminController::class, 'assignCoachToGroupe']);

        // Joueurs
        Route::get('/joueurs', [SuperAdminController::class, 'joueurs']);
        Route::post('/joueurs', [SuperAdminController::class, 'storeJoueur']);
        Route::put('/joueurs/{joueur}', [SuperAdminController::class, 'updateJoueur']);
        Route::delete('/joueurs/{joueur}', [SuperAdminController::class, 'destroyJoueur']);
        Route::post('/joueurs/{joueur}/assign-categorie', [SuperAdminController::class, 'assignJoueurToCategorie']);

        // Paiements
        Route::get('/paiements', [SuperAdminController::class, 'paiements']);
        Route::post('/paiements/{paiement}/valider', [SuperAdminController::class, 'validerPaiement']);

        // Notifications
        Route::post('/notifications', [SuperAdminController::class, 'storeNotification']);
    });

    // Coach
    Route::prefix('coach')->middleware('role:coach')->group(function () {
        Route::get('/dashboard', [CoachController::class, 'index']);
        Route::get('/groupes/{groupe}', [CoachController::class, 'showGroupe']);
        Route::post('/groupes/{groupe}/assign-joueur', [CoachController::class, 'assignJoueur']);
        Route::post('/groupes/{groupe}/seances', [CoachController::class, 'storeSeance']);
        Route::get('/seances/{seance}/appel', [CoachController::class, 'appel']);
        Route::post('/seances/{seance}/appel', [CoachController::class, 'storeAppel'])->name('coach.seances.storeAppel');
        Route::post('/evaluations', [CoachController::class, 'storeEvaluation']);
        Route::post('/seances/{seance}/joueurs/{joueur}/evaluation', [CoachController::class, 'storeEvaluationForSeance']);
    });


    Route::prefix('parent')->middleware('role:parent')->group(function () {
        Route::get('/dashboard', [ParentController::class, 'index']);
        Route::get('/joueurs/{joueur}', [ParentController::class, 'showJoueur']);
        Route::post('/paiement/intent', [ParentController::class, 'createStripeIntent']);
        Route::post('/paiement/confirm', [ParentController::class, 'confirmStripeIntent']);
        Route::post('/paiement', [ParentController::class, 'storePaiement']);
    });
});
