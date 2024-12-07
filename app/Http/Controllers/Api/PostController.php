<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    public function store(Request $request)
    {
        try {
            Log::info('Début de la création du post');
            Log::info('Données reçues:', $request->all());

            $validator = Validator::make($request->all(), [
                'content' => 'required|string|max:1000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // Augmenté à 10MB
                'video' => 'nullable|mimes:mp4,mov,avi,mkv,webm|max:102400' // Augmenté à 100MB
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors(),
                    'details' => [
                        'image_max_size' => '10MB',
                        'video_max_size' => '100MB',
                        'allowed_image_types' => ['jpeg', 'png', 'jpg', 'gif', 'webp'],
                        'allowed_video_types' => ['mp4', 'mov', 'avi', 'mkv', 'webm']
                    ]
                ], 422);
            }

            Log::info('Validation passée avec succès');

            // Gestion de l'image
            $imageUrl = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                Log::info('Image reçue:', [
                    'name' => $image->getClientOriginalName(),
                    'size' => $image->getSize(),
                    'mime' => $image->getMimeType()
                ]);

                $imageName = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('posts/images', $imageName, 'public');
                $imageUrl = Storage::url($path);
                Log::info('Image enregistrée:', ['url' => $imageUrl]);
            }

            // Gestion de la vidéo
            $videoUrl = null;
            if ($request->hasFile('video')) {
                $video = $request->file('video');
                Log::info('Vidéo reçue:', [
                    'name' => $video->getClientOriginalName(),
                    'size' => $video->getSize(),
                    'mime' => $video->getMimeType()
                ]);

                $videoName = time() . '_' . $video->getClientOriginalName();
                $path = $video->storeAs('posts/videos', $videoName, 'public');
                $videoUrl = Storage::url($path);
                Log::info('Vidéo enregistrée:', ['url' => $videoUrl]);
            }

            $post = Post::create([
                'content' => $request->content,
                'image_url' => $imageUrl,
                'video_url' => $videoUrl,
                'user_id' => Auth::id(),
                'status' => true
            ]);

            $post->load(['user']);
            Log::info('Post créé avec succès:', ['post_id' => $post->id]);

            return response()->json([
                'message' => 'Post créé avec succès',
                'post' => $post
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du post:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Une erreur est survenue lors de la création du post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            Log::info('Début de la récupération des posts');
            
            $posts = Post::with(['user', 'comments.user', 'likes'])
                        ->withCount(['likes', 'comments'])
                        ->where('status', true)
                        ->orderBy('created_at', 'desc')
                        ->get();

            foreach ($posts as $post) {
                // Ajout des URLs complètes pour les médias
                if ($post->image_url) {
                    $post->image_url = str_starts_with($post->image_url, 'http') 
                        ? $post->image_url 
                        : asset('storage/' . ltrim($post->image_url, '/'));
                }
                if ($post->video_url) {
                    $post->video_url = str_starts_with($post->video_url, 'http') 
                        ? $post->video_url 
                        : asset('storage/' . ltrim($post->video_url, '/'));
                }
                
                $post->is_liked = $post->likes->contains('user_id', Auth::id());
                unset($post->likes);
            }

            return response()->json($posts);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des posts', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $post = Post::findOrFail($id);
            
            if ($post->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
                return response()->json(['message' => 'Non autorisé'], 403);
            }

            if ($post->image_url) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $post->image_url));
            }

            if ($post->video_url) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $post->video_url));
            }

            $post->delete();

            return response()->json(['message' => 'Post supprimé avec succès']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du post:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Une erreur est survenue lors de la suppression du post',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
