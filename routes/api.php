<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminManagementController;
use App\Http\Controllers\Api\FormateurController;
use App\Http\Controllers\Api\AuthorizedTrainerEmailController;
use App\Http\Controllers\Api\PostController;

// Routes publiques d'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Routes pour les posts
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);

    // Routes pour le super admin
    Route::middleware('role:super_admin')->group(function () {
        // Gestion des admin et info_manager
        Route::post('/admins', [AdminManagementController::class, 'createAdmin']);
        Route::get('/admins', [AdminManagementController::class, 'listAdmins']);
        Route::delete('/admins/{id}', [AdminManagementController::class, 'deleteAdmin']);
    });

    // Routes pour l'admin
    Route::middleware('role:admin')->group(function () {
        // Gestion des formateurs
        Route::get('/formateurs', [FormateurController::class, 'index']);
        Route::post('/formateurs', [FormateurController::class, 'store']);
        Route::put('/formateurs/{id}', [FormateurController::class, 'update']);
        Route::delete('/formateurs/{id}', [FormateurController::class, 'destroy']);

        // Routes pour la gestion des emails de formateurs (admin seulement)
        Route::get('/trainer-emails', [AuthorizedTrainerEmailController::class, 'index']);
        Route::post('/trainer-emails', [AuthorizedTrainerEmailController::class, 'store']);
        Route::delete('/trainer-emails/{id}', [AuthorizedTrainerEmailController::class, 'destroy']);
    });
});