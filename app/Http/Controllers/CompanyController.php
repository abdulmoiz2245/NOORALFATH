<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $company = Company::first();
        
        if (!$company) {
            return redirect()->route('company.create');
        }

        return Inertia::render('company/Show', [
            'company' => $company
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('company/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'bank_details' => 'nullable|array',
        ]);

        Company::create($validated);

        return redirect()->route('company.index')
            ->with('success', 'Company profile created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company): Response
    {
        return Inertia::render('company/Show', [
            'company' => $company
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company): Response
    {
        return Inertia::render('company/Edit', [
            'company' => $company
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'bank_details' => 'nullable|array',
        ]);

        $company->update($validated);

        return redirect()->route('company.index')
            ->with('success', 'Company profile updated successfully.');
    }
}
