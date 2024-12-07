<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return response()->json([]);
        }

        $users = User::where(function($q) use ($query) {
            $q->where('username', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%");
        })
        ->where('status', true)
        ->select('id', 'username', 'email', 'role')
        ->limit(10)
        ->get();

        // Transformer les résultats pour correspondre au format attendu par le frontend
        $transformedUsers = $users->map(function($user) {
            return [
                'id' => $user->id,
                'firstName' => $user->username, // Utiliser username comme nom
                'lastName' => '', // Pas de lastName dans notre modèle
                'email' => $user->email,
                'role' => $user->role,
                'photoUrl' => null // Pas de photo pour l'instant
            ];
        });

        return response()->json($transformedUsers);
    }
}
