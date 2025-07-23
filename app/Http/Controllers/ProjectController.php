<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $projects = Project::with(['client'])
            ->withCount(['invoices'])
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'client' => $project->client->name,
                    'client_id' => $project->client_id,
                    'status' => $project->status,
                    'start_date' => $project->start_date,
                    'end_date' => $project->end_date,
                    'budget' => $project->budget,
                    'invoices_count' => $project->invoices_count,
                    'created_at' => $project->created_at,
                ];
            });

        return Inertia::render('projects/Index', [
            'projects' => $projects
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $clients = Client::select('id', 'name')->get();
        
        return Inertia::render('projects/Create', [
            'clients' => $clients
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|in:planning,in_progress,completed,cancelled',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'budget' => 'nullable|numeric|min:0',
        ]);

        Project::create($validated);

        return redirect()->route('projects.index')
            ->with('message', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): Response
    {
        $project->load(['client', 'invoices.invoiceItems']);
        
        return Inertia::render('projects/Show', [
            'project' => $project
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project): Response
    {
        $clients = Client::select('id', 'name')->get();
        
        return Inertia::render('projects/Edit', [
            'project' => $project,
            'clients' => $clients
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|in:planning,pending,in_progress,completed,cancelled',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'estimated_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',

        ]);

        $project->update($validated);

        return redirect()->route('projects.index')
            ->with('message', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')
            ->with('message', 'Project deleted successfully.');
    }
}
