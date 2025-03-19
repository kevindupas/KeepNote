<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes publiques pour l'authentification
Route::middleware(['api'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/auth/qr-login/{token}', [App\Http\Controllers\Api\QrAuthController::class, 'authenticate']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::get('/test', function () {
        return response()->json(['message' => 'Hello World!']);
    });
});

// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
// Route::post('/reset-password', [AuthController::class, 'resetPassword']);


// Routes protégées avec middleware d'authentification et de tracking API
Route::middleware(['auth:sanctum', \App\Http\Middleware\TrackApiRequests::class])->group(function () {
    // Route pour récupérer l'utilisateur connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Routes pour les notes
    Route::apiResource('notes', NoteController::class);
    Route::get('/shared-notes', [NoteController::class, 'sharedNotes']);

    // Routes pour les tâches
    Route::apiResource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle']);

    // Routes pour les sous-tâches
    Route::post('/tasks/{task}/subtasks', [TaskController::class, 'addSubtask']);
    Route::put('/tasks/{task}/subtasks/{subtask}', [TaskController::class, 'updateSubtask']);
    Route::delete('/tasks/{task}/subtasks/{subtask}', [TaskController::class, 'removeSubtask']);

    // Routes pour les catégories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // Route de déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);
});
