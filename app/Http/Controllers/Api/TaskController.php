<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Récupérer toutes les tâches de l'utilisateur
     */
    public function index(Request $request)
    {
        $query = Task::with('note')
            ->where('user_id', Auth::id());

        // Filtre optionnel par note_id
        if ($request->has('note_id')) {
            $query->where('note_id', $request->note_id);
        }

        // Filtre optionnel par statut
        if ($request->has('is_completed')) {
            $query->where('is_completed', $request->is_completed);
        }

        $tasks = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $tasks
        ]);
    }

    /**
     * Créer une nouvelle tâche
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255',
            'note_id' => 'nullable|exists:notes,id',
            'is_completed' => 'boolean',
            'subtasks' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier que la note appartient à l'utilisateur, seulement si une note_id est fournie
        if ($request->filled('note_id')) {
            $note = Note::where('id', $request->note_id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$note) {
                return response()->json([
                    'message' => 'Note non trouvée ou vous n\'avez pas les droits nécessaires'
                ], 403);
            }
        }

        $task = Task::create([
            'description' => $request->description,
            'note_id' => $request->filled('note_id') ? $request->note_id : null,
            'user_id' => Auth::id(),
            'is_completed' => $request->is_completed ?? false,
            'subtasks' => $request->subtasks ?? [],
        ]);

        return response()->json([
            'message' => 'Tâche créée avec succès',
            'data' => $task
        ], 201);
    }

    /**
     * Récupérer les détails d'une tâche spécifique
     */
    public function show($id)
    {
        $task = Task::with('note')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$task) {
            return response()->json([
                'message' => 'Tâche non trouvée'
            ], 404);
        }

        return response()->json([
            'data' => $task
        ]);
    }

    /**
     * Mettre à jour une tâche existante
     */
    public function update(Request $request, $id)
    {
        $task = Task::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$task) {
            return response()->json([
                'message' => 'Tâche non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255',
            'note_id' => 'nullable|exists:notes,id',
            'is_completed' => 'boolean',
            'subtasks' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Si le note_id est modifié et n'est pas null, vérifier que la nouvelle note appartient à l'utilisateur
        if ($request->filled('note_id') && $request->note_id != $task->note_id) {
            $note = Note::where('id', $request->note_id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$note) {
                return response()->json([
                    'message' => 'Note non trouvée ou vous n\'avez pas les droits nécessaires'
                ], 403);
            }
        }

        $task->update([
            'description' => $request->description,
            'note_id' => $request->has('note_id') ? $request->note_id : $task->note_id,
            'is_completed' => $request->is_completed ?? $task->is_completed,
            'subtasks' => $request->subtasks ?? $task->subtasks,
        ]);

        return response()->json([
            'message' => 'Tâche mise à jour avec succès',
            'data' => $task->fresh()
        ]);
    }

    /**
     * Supprimer une tâche
     */
    public function destroy($id)
    {
        $task = Task::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$task) {
            return response()->json([
                'message' => 'Tâche non trouvée'
            ], 404);
        }

        $task->delete();

        return response()->json([
            'message' => 'Tâche supprimée avec succès'
        ]);
    }

    /**
     * Basculer l'état d'une tâche (complétée / non complétée)
     */
    public function toggle($id)
    {
        $task = Task::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$task) {
            return response()->json([
                'message' => 'Tâche non trouvée'
            ], 404);
        }

        $task->update([
            'is_completed' => !$task->is_completed,
        ]);

        return response()->json([
            'message' => 'État de la tâche modifié avec succès',
            'data' => $task->fresh()
        ]);
    }

    /**
     * Ajouter une sous-tâche à une tâche existante
     */
    public function addSubtask(Request $request, $id)
    {
        $task = Task::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$task) {
            return response()->json([
                'message' => 'Tâche non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255',
            'is_completed' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $subtasks = $task->subtasks ?? [];
        $subtasks[] = [
            'id' => count($subtasks) + 1,
            'description' => $request->description,
            'is_completed' => $request->is_completed ?? false,
        ];

        $task->update(['subtasks' => $subtasks]);

        return response()->json([
            'message' => 'Sous-tâche ajoutée avec succès',
            'data' => $task->fresh()
        ]);
    }

    /**
     * Mettre à jour une sous-tâche
     */
    public function updateSubtask(Request $request, $id, $subtaskId)
    {
        $task = Task::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$task) {
            return response()->json([
                'message' => 'Tâche non trouvée'
            ], 404);
        }

        $subtasks = $task->subtasks ?? [];
        $subtaskIndex = null;

        foreach ($subtasks as $index => $subtask) {
            if ($subtask['id'] == $subtaskId) {
                $subtaskIndex = $index;
                break;
            }
        }

        if ($subtaskIndex === null) {
            return response()->json([
                'message' => 'Sous-tâche non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'string|max:255',
            'is_completed' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('description')) {
            $subtasks[$subtaskIndex]['description'] = $request->description;
        }

        if ($request->has('is_completed')) {
            $subtasks[$subtaskIndex]['is_completed'] = $request->is_completed;
        }

        $task->update(['subtasks' => $subtasks]);

        return response()->json([
            'message' => 'Sous-tâche mise à jour avec succès',
            'data' => $task->fresh()
        ]);
    }

    /**
     * Supprimer une sous-tâche
     */
    public function removeSubtask($id, $subtaskId)
    {
        $task = Task::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$task) {
            return response()->json([
                'message' => 'Tâche non trouvée'
            ], 404);
        }

        $subtasks = $task->subtasks ?? [];
        $found = false;

        $newSubtasks = array_filter($subtasks, function ($subtask) use ($subtaskId, &$found) {
            if ($subtask['id'] == $subtaskId) {
                $found = true;
                return false;
            }
            return true;
        });

        if (!$found) {
            return response()->json([
                'message' => 'Sous-tâche non trouvée'
            ], 404);
        }

        // Réindexer le tableau
        $task->update(['subtasks' => array_values($newSubtasks)]);

        return response()->json([
            'message' => 'Sous-tâche supprimée avec succès',
            'data' => $task->fresh()
        ]);
    }
}
