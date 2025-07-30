<?php

namespace App\Http\Controllers;

use App\Models\ServiceReport;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $serviceReports = ServiceReport::with('client')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('ServiceReports/Index', [
            'serviceReports' => $serviceReports
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::select('id', 'name')->orderBy('name')->get();
        
        return Inertia::render('ServiceReports/Create', [
            'clients' => $clients,
            'serviceReportNumber' => ServiceReport::generateServiceReportNumber()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_date' => 'required|date',
            'ac_work' => 'boolean',
            'plumbing_work' => 'boolean',
            'paint_work' => 'boolean',
            'electrical_work' => 'boolean',
            'civil_work' => 'boolean',
            'job_details' => 'required|string|max:5000',
        ]);

        $validated['service_report_number'] = ServiceReport::generateServiceReportNumber();

        $serviceReport = ServiceReport::create($validated);

        return redirect()->route('service-reports.index')
            ->with('success', 'Service report created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceReport $serviceReport)
    {
        $serviceReport->load('client');
        
        return Inertia::render('ServiceReports/Show', [
            'serviceReport' => $serviceReport
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceReport $serviceReport)
    {
        $clients = Client::select('id', 'name')->orderBy('name')->get();
        $serviceReport->load('client');
        
        return Inertia::render('ServiceReports/Edit', [
            'serviceReport' => $serviceReport,
            'clients' => $clients
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceReport $serviceReport)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_date' => 'required|date',
            'ac_work' => 'boolean',
            'plumbing_work' => 'boolean',
            'paint_work' => 'boolean',
            'electrical_work' => 'boolean',
            'civil_work' => 'boolean',
            'job_details' => 'required|string|max:5000',
        ]);

        $serviceReport->update($validated);

        return redirect()->route('service-reports.index')
            ->with('success', 'Service report updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceReport $serviceReport)
    {
        $serviceReport->delete();

        return redirect()->route('service-reports.index')
            ->with('success', 'Service report deleted successfully.');
    }
}
