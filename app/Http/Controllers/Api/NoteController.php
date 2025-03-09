<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{
    /**
     * Récupérer toutes les notes de l'utilisateur
     */
    public function index()
    {
        $notes = Note::with('categories')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $notes
        ]);
    }

    /**
     * Créer une nouvelle note
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $note = Note::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => Auth::id(),
        ]);

        if ($request->has('categories')) {
            $note->categories()->attach($request->categories);
        }

        return response()->json([
            'message' => 'Note créée avec succès',
            'data' => $note->load('categories')
        ], 201);
    }

    /**
     * Récupérer les détails d'une note spécifique
     */
    public function show($id)
    {
        $note = Note::with(['categories', 'tasks'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$note) {
            return response()->json([
                'message' => 'Note non trouvée'
            ], 404);
        }

        return response()->json([
            'data' => $note
        ]);
    }

    /**
     * Mettre à jour une note existante
     */
    public function update(Request $request, $id)
    {
        $note = Note::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$note) {
            return response()->json([
                'message' => 'Note non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $note->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        if ($request->has('categories')) {
            $note->categories()->sync($request->categories);
        }

        return response()->json([
            'message' => 'Note mise à jour avec succès',
            'data' => $note->load('categories')
        ]);
    }

    /**
     * Supprimer une note
     */
    public function destroy($id)
    {
        $note = Note::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$note) {
            return response()->json([
                'message' => 'Note non trouvée'
            ], 404);
        }

        // Supprimer les tâches associées
        $note->tasks()->delete();

        // Détacher les catégories
        $note->categories()->detach();

        // Supprimer la note
        $note->delete();

        return response()->json([
            'message' => 'Note supprimée avec succès'
        ]);
    }

    /**
     * Récupérer les notes partagées par d'autres utilisateurs
     */
    public function sharedNotes()
    {
        $sharedNotes = Note::with(['categories', 'user'])
            ->where('is_shared', true)
            ->where('user_id', '!=', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $sharedNotes
        ]);
    }
}
