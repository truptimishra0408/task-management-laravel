<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['creator', 'tasks'])->get();
        return response()->json(['success' => true, 'data' => $projects]);
    }

    public function store(StoreProjectRequest $request)
    {
        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => auth()->id(),
        ]);
        return response()->json(['success' => true, 'data' => $project], 201);
    }

    public function show($id)
    {
        $project = Project::with(['tasks.assignedUser'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $project]);
    }
}