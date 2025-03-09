<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Récupérer toutes les catégories (système et personnelles)
     */
    public function index()
    {
        // Récupérer les catégories système et les catégories personnelles de l'utilisateur
        $categories = Category::where('is_system', true)
            ->orWhere(function ($query) {
                $query->where('is_system', false)
                    ->where('user_id', Auth::id());
            })
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $categories
        ]);
    }

    /**
     * Créer une nouvelle catégorie personnelle
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category = Category::create([
            'name' => $request->name,
            'color' => $request->color,
            'is_system' => false, // Les utilisateurs ne peuvent pas créer de catégories système
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Catégorie créée avec succès',
            'data' => $category
        ], 201);
    }

    /**
     * Mettre à jour une catégorie existante
     */
    public function update(Request $request, $id)
    {
        $category = Category::where(function ($query) use ($id) {
            $query->where('id', $id)
                ->where(function ($q) {
                    $q->where('is_system', false) // Autoriser la modification des catégories non-système
                        ->where('user_id', Auth::id()); // qui appartiennent à l'utilisateur
                });
        })
            ->first();

        if (!$category) {
            return response()->json([
                'message' => 'Catégorie non trouvée ou non modifiable'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category->update([
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return response()->json([
            'message' => 'Catégorie mise à jour avec succès',
            'data' => $category->fresh()
        ]);
    }

    /**
     * Supprimer une catégorie
     */
    public function destroy($id)
    {
        $category = Category::where(function ($query) use ($id) {
            $query->where('id', $id)
                ->where(function ($q) {
                    $q->where('is_system', false) // Autoriser la suppression des catégories non-système
                        ->where('user_id', Auth::id()); // qui appartiennent à l'utilisateur
                });
        })
            ->first();

        if (!$category) {
            return response()->json([
                'message' => 'Catégorie non trouvée ou non supprimable'
            ], 404);
        }

        // Détacher la catégorie de toutes les notes avant de la supprimer
        $category->notes()->detach();

        $category->delete();

        return response()->json([
            'message' => 'Catégorie supprimée avec succès'
        ]);
    }
}
