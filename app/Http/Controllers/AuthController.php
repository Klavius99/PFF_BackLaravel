<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AuthorizedTrainerEmail;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        // Validation des données avec condition sur l'email
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'regex:/^[a-z0-9._%+-]+@isepdiamniadio\.edu\.sn$/',
                'unique:users,email',
            ],
            'password' => 'required|string|min:8|confirmed',
        ], [
            // Messages d'erreur personnalisés
            'email.regex' => 'L\'adresse email doit être un email de l\'ISEP (exemple : sn.fall4@isepdiamniadio.edu.sn).',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
        ]);

        // Vérifier si l'email est dans la liste des formateurs autorisés
        $isTrainer = AuthorizedTrainerEmail::where('email', $validated['email'])->exists();
        
        // Création de l'utilisateur avec le rôle approprié
        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $isTrainer ? 'formateur' : 'apprenant',
            'status' => true,
        ]);

        // Génération du token pour l'utilisateur
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inscription réussie !',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Les identifiants sont incorrects.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie !',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie !'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }
}
