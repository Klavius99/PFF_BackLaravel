<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuthorizedTrainerEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthorizedTrainerEmailController extends Controller
{
    public function index()
    {
        return response()->json(AuthorizedTrainerEmail::all());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:authorized_trainer_emails', 'regex:/^[a-zA-Z0-9._%+-]+@isepdiamniadio\.edu\.sn$/i'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = AuthorizedTrainerEmail::create([
            'email' => $request->email
        ]);

        return response()->json([
            'message' => 'Email de formateur ajouté avec succès',
            'email' => $email
        ], 201);
    }

    public function destroy($id)
    {
        $email = AuthorizedTrainerEmail::findOrFail($id);
        $email->delete();

        return response()->json([
            'message' => 'Email de formateur supprimé avec succès'
        ]);
    }
}
