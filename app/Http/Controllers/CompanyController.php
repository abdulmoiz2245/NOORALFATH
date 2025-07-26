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
        $bankDetails = json_decode($company->bank_details, true);
        $company->bank_details = $bankDetails;
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
            'logo' => 'nullable|string',
            'signature' => 'nullable|string',
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
    public function edit(Company $company = null)
    {
        // If no company is passed, get the first one (since there should only be one company profile)
        if (!$company) {
            $company = Company::first();
        }
        $bankDetails = json_decode($company->bank_details, true);
        $company->bank_details = $bankDetails;
        if (!$company) {
            return redirect()->route('company.create');
        }

        return Inertia::render('company/Edit', [
            'company' => $company
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company = null)
    {
        // If no company is passed, get the first one (since there should only be one company profile)
        if (!$company) {
            $company = Company::first();
        }
        
        if (!$company) {
            return redirect()->route('company.create')
                ->with('error', 'No company profile found. Please create one first.');
        }


        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'account_holder' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:255',
            'iban_number' => 'nullable|string|max:255',
            'logo' => 'nullable|string',
            'signature' => 'nullable|string',
        ]);

        // Convert bank details to JSON if provided
        if (isset($validated['bank_name']) || isset($validated['account_number']) ||
            isset($validated['account_holder']) || isset($validated['swift_code']) ||
            isset($validated['iban_number'])) {
            $validated['bank_details'] = json_encode([
                'bank_name' => $validated['bank_name'] ?? null,
                'account_number' => $validated['account_number'] ?? null,
                'account_holder' => $validated['account_holder'] ?? null,
                'swift_code' => $validated['swift_code'] ?? null,
                'iban_number' => $validated['iban_number'] ?? null,
            ]);
        }

        $company->update($validated);

        return redirect()->route('company.index')
            ->with('success', 'Company profile updated successfully.');
    }
}
