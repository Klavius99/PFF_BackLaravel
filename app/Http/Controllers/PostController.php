<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')->orderBy('created_at', 'desc')->get();
        return response()->json($posts);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'content' => 'nullable|string|max:1000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $post = new Post();
            $post->user_id = Auth::id();
            $post->content = $request->content;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/posts', $imageName);
                $post->image_url = 'storage/posts/' . $imageName;
            }

            $post->save();

            return response()->json([
                'message' => 'Post créé avec succès',
                'post' => $post->load('user')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du post: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur est survenue lors de la création du post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $post = Post::findOrFail($id);
            
            if ($post->user_id !== Auth::id()) {
                return response()->json(['message' => 'Non autorisé'], 403);
            }

            if ($post->image_url) {
                $imagePath = str_replace('storage/', 'public/', $post->image_url);
                Storage::delete($imagePath);
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
