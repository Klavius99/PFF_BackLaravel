<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Formateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FormateurController extends Controller
{
    public function index()
    {
        return response()->json(Formateur::all());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:formateurs', 'regex:/^[a-zA-Z0-9._%+-]+@isepdiamniadio\.edu\.sn$/i'],
            'telephone' => 'nullable|string|max:20',
            'specialite' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $formateur = Formateur::create($request->all());

        return response()->json([
            'message' => 'Formateur ajouté avec succès',
            'formateur' => $formateur
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $formateur = Formateur::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@isepdiamniadio\.edu\.sn$/i',
                'unique:formateurs,email,' . $id
            ],
            'telephone' => 'nullable|string|max:20',
            'specialite' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $formateur->update($request->all());

        return response()->json([
            'message' => 'Formateur mis à jour avec succès',
            'formateur' => $formateur
        ]);
    }

    public function destroy($id)
    {
        $formateur = Formateur::findOrFail($id);
        
        if ($formateur->est_inscrit) {
            return response()->json([
                'error' => 'Impossible de supprimer un formateur déjà inscrit sur la plateforme'
            ], 400);
        }

        $formateur->delete();

        return response()->json([
            'message' => 'Formateur supprimé avec succès'
        ]);
    }
}
