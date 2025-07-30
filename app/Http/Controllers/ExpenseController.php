<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Vendor;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $expenses = Expense::with(['vendor', 'project'])
            ->latest()
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'description' => $expense->description,
                    'amount' => $expense->amount,
                    'expense_date' => $expense->expense_date,
                    'category' => $expense->category,
                    'vendor' => $expense->vendor ? $expense->vendor->name : null,
                    'vendor_id' => $expense->vendor_id,
                    'project' => $expense->project ? $expense->project->name : null,
                    'project_id' => $expense->project_id,
                    'receipt_path' => $expense->receipt_path,
                    'is_billable' => $expense->is_billable,
                    'is_reimbursable' => $expense->is_reimbursable,
                    'created_at' => $expense->created_at,
                ];
            });

        return Inertia::render('expenses/Index', [
            'expenses' => $expenses
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $vendors = Vendor::select('id', 'name')->get();
        $projects = Project::with('client')->select('id', 'name', 'client_id')->get();
        
        return Inertia::render('expenses/Create', [
            'vendors' => $vendors,
            'projects' => $projects
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'category' => 'required|string|max:255',
            'vendor_id' => 'nullable|exists:vendors,id',
            'project_id' => 'nullable|exists:projects,id',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'is_billable' => 'boolean',
            'is_reimbursable' => 'boolean',
        ]);

        // Handle file upload
        if ($request->hasFile('receipt')) {
            $validated['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
        }

        $validated['expense_date'] = $validated['date'];

        Expense::create($validated);

        return redirect()->route('expenses.index')
            ->with('message', 'Expense created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense): Response
    {
        $expense->load(['vendor', 'project']);
        
        return Inertia::render('expenses/Show', [
            'expense' => $expense
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense): Response
    {
        $vendors = Vendor::select('id', 'name')->get();
        $projects = Project::with('client')->select('id', 'name', 'client_id')->get();
        
        return Inertia::render('expenses/Edit', [
            'expense' => $expense,
            'vendors' => $vendors,
            'projects' => $projects
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'required|string|max:255',
            'vendor_id' => 'nullable|exists:vendors,id',
            'project_id' => 'nullable|exists:projects,id',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'is_billable' => 'boolean',
            'is_reimbursable' => 'boolean',
        ]);

        // dd($validated);

        // Handle file upload
        if ($request->hasFile('receipt')) {
            // Delete old receipt if exists
            if ($expense->receipt_path) {
                \Storage::disk('public')->delete($expense->receipt_path);
            }
            $validated['receipt_file'] = $request->file('receipt')->store('receipts', 'public');
        }

        // $validated['expense_date'] = $validated['date'];


        $expense->update($validated);

        return redirect()->route('expenses.index')
            ->with('message', 'Expense updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        // Delete receipt file if exists
        if ($expense->receipt_path) {
            \Storage::disk('public')->delete($expense->receipt_path);
        }

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('message', 'Expense deleted successfully.');
    }
}
