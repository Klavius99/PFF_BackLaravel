<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminManagementController;
use App\Http\Controllers\Api\FormateurController;
use App\Http\Controllers\Api\AuthorizedTrainerEmailController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserSearchController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\LikeController;

// Routes publiques d'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Route de recherche d'utilisateurs
    Route::get('/users/search', [UserSearchController::class, 'search']);

    // Routes pour les posts
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);

    // Routes pour les commentaires
    Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
    Route::post('/posts/{postId}/comments', [CommentController::class, 'store']);
    Route::delete('/posts/{postId}/comments/{commentId}', [CommentController::class, 'destroy']);

    // Routes pour les likes
    Route::post('/posts/{postId}/like', [LikeController::class, 'toggle']);
    Route::get('/posts/{postId}/like/check', [LikeController::class, 'check']);
    Route::get('/posts/{postId}/likes/count', [LikeController::class, 'count']);

    // Routes pour l'admin
    Route::middleware('role:admin')->group(function () {
        // Gestion des admin et info_manager
        Route::post('/admins', [AdminManagementController::class, 'createAdmin']);
        Route::get('/admins', [AdminManagementController::class, 'listAdmins']);
        Route::delete('/admins/{id}', [AdminManagementController::class, 'deleteAdmin']);
        
        // Gestion des formateurs
        Route::get('/formateurs', [FormateurController::class, 'index']);
        Route::post('/formateurs', [FormateurController::class, 'store']);
        Route::delete('/formateurs/{id}', [FormateurController::class, 'destroy']);
        
        // Gestion des emails autorisés pour les formateurs
        Route::get('/authorized-emails', [AuthorizedTrainerEmailController::class, 'index']);
        Route::post('/authorized-emails', [AuthorizedTrainerEmailController::class, 'store']);
        Route::delete('/authorized-emails/{id}', [AuthorizedTrainerEmailController::class, 'destroy']);
    });
});
