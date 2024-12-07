<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function store(Request $request, $postId)
    {
        try {
            Log::info('Début de la création du commentaire pour le post ' . $postId);
            Log::info('Données reçues:', $request->all());

            // Validation
            $request->validate([
                'content' => 'required|string|max:1000'
            ]);

            // Vérifier si le post existe
            $post = Post::findOrFail($postId);

            // Créer le commentaire
            $comment = Comment::create([
                'content' => $request->content,
                'user_id' => Auth::id(),
                'post_id' => $postId
            ]);

            // Charger les relations
            $comment->load('user');

            Log::info('Commentaire créé avec succès, ID: ' . $comment->id);

            return response()->json([
                'message' => 'Commentaire ajouté avec succès',
                'comment' => $comment
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du commentaire: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Une erreur est survenue lors de la création du commentaire',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index($postId)
    {
        try {
            $comments = Comment::with('user')
                ->where('post_id', $postId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($comments);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des commentaires: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des commentaires',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($postId, $commentId)
    {
        try {
            $comment = Comment::findOrFail($commentId);

            // Vérifier si l'utilisateur est autorisé à supprimer le commentaire
            if ($comment->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
                return response()->json(['message' => 'Non autorisé'], 403);
            }

            $comment->delete();

            return response()->json(['message' => 'Commentaire supprimé avec succès']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du commentaire: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Une erreur est survenue lors de la suppression du commentaire',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
