<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Task::with(['project', 'assignedUser']);

        if (!$user->isAdmin()) {
            $query->where('assigned_to', $user->id);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function store(StoreTaskRequest $request)
    {
        $task = Task::create($request->validated() + ['status' => 'TODO']);
        return response()->json(['success' => true, 'data' => $task], 201);
    }

    public function update(UpdateTaskRequest $request, $id)
    {
        $task = Task::findOrFail($id);
        $user = $request->user();
        $newStatus = $request->status;

        if (!$user->isAdmin() && $task->assigned_to !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($task->status === 'OVERDUE') {
            if ($newStatus === 'IN_PROGRESS') {
                return response()->json(['success' => false, 'message' => 'Overdue tasks cannot move back to IN_PROGRESS'], 422);
            }
            if ($newStatus === 'DONE' && !$user->isAdmin()) {
                return response()->json(['success' => false, 'message' => 'Only admin can close overdue tasks'], 403);
            }
        }

        $task->update(['status' => $newStatus]);
        return response()->json(['success' => true, 'data' => $task]);
    }
}