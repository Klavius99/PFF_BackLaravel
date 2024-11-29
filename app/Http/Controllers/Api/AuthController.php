<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Formateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            Log::info('Starting registration process', ['request_data' => $request->all()]);
            
            $validator = Validator::make($request->all(), [
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'regex:/^[a-zA-Z0-9._%+-]+@isepdiamniadio\.edu\.sn$/i'],
                'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed during registration', [
                    'errors' => $validator->errors(),
                    'request_data' => $request->all()
                ]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Vérifier si c'est un formateur qui s'inscrit
            $formateur = Formateur::where('email', $request->email)->first();
            Log::info('Checking formateur status', [
                'email' => $request->email,
                'formateur_exists' => (bool)$formateur,
                'formateur_data' => $formateur
            ]);

            $role = 'apprenant';
            if ($formateur) {
                if ($formateur->est_inscrit) {
                    Log::warning('Attempt to register with already registered formateur email', [
                        'email' => $request->email,
                        'formateur' => $formateur
                    ]);
                    return response()->json(['errors' => ['email' => ['Ce compte formateur est déjà inscrit.']]], 422);
                }
                $role = 'formateur';
                $formateur->est_inscrit = true;
                $formateur->save();
                Log::info('Formateur marked as registered', [
                    'email' => $request->email,
                    'formateur_id' => $formateur->id
                ]);
            }

            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $role,
                'status' => true,
            ]);

            Log::info('User created successfully', ['user_id' => $user->id, 'role' => $role]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'message' => 'Inscription réussie'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Registration error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'inscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'message' => 'Email ou mot de passe incorrect'
                ], 401);
            }

            $user = User::where('email', $request->email)->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'message' => 'Connexion réussie'
            ]);

        } catch (\Exception $e) {
            Log::error('Login error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'message' => 'Une erreur est survenue lors de la connexion'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Déconnexion réussie']);
        } catch (\Exception $e) {
            Log::error('Logout error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Une erreur est survenue lors de la déconnexion'], 500);
        }
    }

    public function user(Request $request)
    {
        try {
            return response()->json($request->user());
        } catch (\Exception $e) {
            Log::error('User fetch error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération des informations utilisateur'], 500);
        }
    }
}
