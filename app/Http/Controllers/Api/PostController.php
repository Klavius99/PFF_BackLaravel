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
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'video' => 'nullable|mimes:mp4,mov,avi|max:20480'
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json(['errors' => $validator->errors()], 422);
            }

            Log::info('Validation passée avec succès');

            $imageUrl = null;
            if ($request->hasFile('image')) {
                Log::info('Processing image upload');
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('posts', $imageName, 'public');
                $imageUrl = Storage::url($path);
                Log::info('Image stored at: ' . $imageUrl);
            }

            $videoUrl = null;
            if ($request->hasFile('video')) {
                Log::info('Processing video upload');
                $video = $request->file('video');
                $videoName = time() . '_' . $video->getClientOriginalName();
                $path = $video->storeAs('posts/videos', $videoName, 'public');
                $videoUrl = Storage::url($path);
                Log::info('Video stored at: ' . $videoUrl);
            }

            Log::info('Creating post in database');
            $post = Post::create([
                'content' => $request->content,
                'image_url' => $imageUrl,
                'video_url' => $videoUrl,
                'user_id' => Auth::id(),
                'status' => true
            ]);

            $post->load('user');
            Log::info('Post created successfully with ID: ' . $post->id);

            return response()->json([
                'message' => 'Post créé avec succès',
                'post' => $post
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating post: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Une erreur est survenue lors de la création du post',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $posts = Post::with('user')
                        ->where('status', true)
                        ->orderBy('created_at', 'desc')
                        ->get();

            return response()->json($posts);
        } catch (\Exception $e) {
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
            
            if ($post->user_id !== Auth::id() && !Auth::user()->hasRole(['admin', 'super_admin'])) {
                return response()->json(['message' => 'Non autorisé'], 403);
            }

            if ($post->image_url) {
                $path = str_replace('/storage/', '', $post->image_url);
                Storage::disk('public')->delete($path);
            }

            $post->delete();

            return response()->json(['message' => 'Post supprimé avec succès']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la suppression du post',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
