<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Assuming you have a User model
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    /**
     * Create a new admin user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createAdmin(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $admin = User::create([
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'admin',
            'status' => true,
        ]);

        return response()->json([
            'message' => 'Admin créé avec succès',
            'admin' => $admin
        ], 201);
    }

    /**
     * List all admin users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listAdmins()
    {
        $admins = User::where('role', 'admin')->get();

        return response()->json($admins);
    }

    /**
     * Delete an admin user
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAdmin($id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);
        $admin->delete();

        return response()->json([
            'message' => 'Admin deleted successfully'
        ]);
    }

    public function createInfoManager(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $infoManager = User::create([
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'info_manager',
            'status' => true,
        ]);

        return response()->json([
            'message' => 'Info Manager créé avec succès',
            'info_manager' => $infoManager
        ], 201);
    }

    public function listInfoManagers()
    {
        $infoManagers = User::where('role', 'info_manager')->get();
        return response()->json($infoManagers);
    }

    public function deleteInfoManager($id)
    {
        $infoManager = User::where('role', 'info_manager')->findOrFail($id);
        $infoManager->delete();

        return response()->json([
            'message' => 'Info Manager supprimé avec succès'
        ]);
    }
}
