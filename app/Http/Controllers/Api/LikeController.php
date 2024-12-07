<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LikeController extends Controller
{
    public function toggle(Request $request, $postId)
    {
        try {
            Log::info('Toggle like pour le post ' . $postId);
            
            $post = Post::findOrFail($postId);
            $userId = Auth::id();

            $like = Like::where('post_id', $postId)
                       ->where('user_id', $userId)
                       ->first();

            if ($like) {
                $like->delete();
                Log::info('Like supprimé');
                return response()->json([
                    'message' => 'Like retiré',
                    'liked' => false
                ]);
            } else {
                Like::create([
                    'post_id' => $postId,
                    'user_id' => $userId
                ]);
                Log::info('Like ajouté');
                return response()->json([
                    'message' => 'Post liké',
                    'liked' => true
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du toggle like: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function check($postId)
    {
        try {
            $userId = Auth::id();
            $liked = Like::where('post_id', $postId)
                        ->where('user_id', $userId)
                        ->exists();

            return response()->json($liked);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du like: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function count($postId)
    {
        try {
            $count = Like::where('post_id', $postId)->count();
            return response()->json($count);
        } catch (\Exception $e) {
            Log::error('Erreur lors du comptage des likes: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
