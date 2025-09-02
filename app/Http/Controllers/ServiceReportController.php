<?php

namespace App\Http\Controllers;

use App\Models\ServiceReport;
use App\Models\Client;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ServiceReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ServiceReport::with('client');

        // Apply filters
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('service_date_from')) {
            $query->whereDate('service_date', '>=', $request->service_date_from);
        }

        if ($request->filled('service_date_to')) {
            $query->whereDate('service_date', '<=', $request->service_date_to);
        }

        if ($request->filled('service_report_number')) {
            $query->where('service_report_number', 'like', '%' . $request->service_report_number . '%');
        }

        // Work type filters
        if ($request->filled('ac_work')) {
            $query->where('ac_work', true);
        }
        if ($request->filled('plumbing_work')) {
            $query->where('plumbing_work', true);
        }
        if ($request->filled('paint_work')) {
            $query->where('paint_work', true);
        }
        if ($request->filled('electrical_work')) {
            $query->where('electrical_work', true);
        }
        if ($request->filled('civil_work')) {
            $query->where('civil_work', true);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $serviceReports = $query->paginate(15)->withQueryString();

        // Get clients for filter dropdown
        $clients = Client::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('ServiceReports/Index', [
            'serviceReports' => $serviceReports,
            'clients' => $clients,
            'filters' => $request->only([
                'client_id', 'service_date_from', 'service_date_to', 'service_report_number',
                'ac_work', 'plumbing_work', 'paint_work', 'electrical_work', 'civil_work',
                'sort_by', 'sort_direction'
            ])
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
        // Debug: Log incoming request data
        Log::info('Service Report Store - Raw Request Data:', $request->all());
        
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_date' => 'required|date',
            'ac_work' => 'boolean',
            'plumbing_work' => 'boolean',
            'paint_work' => 'boolean',
            'electrical_work' => 'boolean',
            'civil_work' => 'boolean',
            'job_details' => 'required|string|max:5000',
            'before_pictures' => 'nullable|array|max:4',
            'before_pictures.*' => 'nullable|file|mimes:jpeg,jpg,png|max:5120',
            'after_pictures' => 'nullable|array|max:4',
            'after_pictures.*' => 'nullable|file|mimes:jpeg,jpg,png|max:5120',
        ]);

        // Debug: Log validated data before boolean conversion
        Log::info('Service Report Store - Validated Data:', $validated);

        // Ensure boolean fields are properly set
        $validated['ac_work'] = $request->boolean('ac_work');
        $validated['plumbing_work'] = $request->boolean('plumbing_work');
        $validated['paint_work'] = $request->boolean('paint_work');
        $validated['electrical_work'] = $request->boolean('electrical_work');
        $validated['civil_work'] = $request->boolean('civil_work');

        // Handle file uploads
        $beforePictures = [];
        $afterPictures = [];

        if ($request->hasFile('before_pictures')) {
            foreach ($request->file('before_pictures') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('service-reports/before', 'public');
                    $beforePictures[] = $path;
                }
            }
        }

        if ($request->hasFile('after_pictures')) {
            foreach ($request->file('after_pictures') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('service-reports/after', 'public');
                    $afterPictures[] = $path;
                }
            }
        }

        $validated['before_pictures'] = $beforePictures;
        $validated['after_pictures'] = $afterPictures;

        // Debug: Log data after boolean conversion
        Log::info('Service Report Store - After Boolean Conversion:', $validated);

        $validated['service_report_number'] = ServiceReport::generateServiceReportNumber();

        $serviceReport = ServiceReport::create($validated);

        // Debug: Log created service report
        Log::info('Service Report Store - Created Report:', $serviceReport->toArray());

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
            'before_pictures' => 'nullable|array|max:4',
            'before_pictures.*' => 'nullable|file|mimes:jpeg,jpg,png|max:5120',
            'after_pictures' => 'nullable|array|max:4',
            'after_pictures.*' => 'nullable|file|mimes:jpeg,jpg,png|max:5120',
        ]);

        // Ensure boolean fields are properly set
        $validated['ac_work'] = $request->boolean('ac_work');
        $validated['plumbing_work'] = $request->boolean('plumbing_work');
        $validated['paint_work'] = $request->boolean('paint_work');
        $validated['electrical_work'] = $request->boolean('electrical_work');
        $validated['civil_work'] = $request->boolean('civil_work');

        // Handle file uploads
        $beforePictures = $serviceReport->before_pictures ?? [];
        $afterPictures = $serviceReport->after_pictures ?? [];

        // Handle existing pictures (from frontend)
        $existingBeforePictures = $request->input('existing_before_pictures', []);
        $existingAfterPictures = $request->input('existing_after_pictures', []);

        // Delete removed before pictures
        $removedBeforePictures = array_diff($beforePictures, $existingBeforePictures);
        foreach ($removedBeforePictures as $removedPath) {
            if (Storage::disk('public')->exists($removedPath)) {
                Storage::disk('public')->delete($removedPath);
            }
        }

        // Delete removed after pictures
        $removedAfterPictures = array_diff($afterPictures, $existingAfterPictures);
        foreach ($removedAfterPictures as $removedPath) {
            if (Storage::disk('public')->exists($removedPath)) {
                Storage::disk('public')->delete($removedPath);
            }
        }

        // Start with existing pictures
        $beforePictures = $existingBeforePictures;
        $afterPictures = $existingAfterPictures;

        // Add new before pictures
        if ($request->hasFile('before_pictures')) {
            foreach ($request->file('before_pictures') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('service-reports/before', 'public');
                    $beforePictures[] = $path;
                }
            }
        }

        // Add new after pictures
        if ($request->hasFile('after_pictures')) {
            foreach ($request->file('after_pictures') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('service-reports/after', 'public');
                    $afterPictures[] = $path;
                }
            }
        }

        $validated['before_pictures'] = $beforePictures;
        $validated['after_pictures'] = $afterPictures;

        $serviceReport->update($validated);

        return redirect()->route('service-reports.index')
            ->with('success', 'Service report updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceReport $serviceReport)
    {
        // Delete associated files
        if ($serviceReport->before_pictures) {
            foreach ($serviceReport->before_pictures as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }

        if ($serviceReport->after_pictures) {
            foreach ($serviceReport->after_pictures as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }

        $serviceReport->delete();

        return redirect()->route('service-reports.index')
            ->with('success', 'Service report deleted successfully.');
    }

    /**
     * Download PDF for service report
     */
    public function downloadPdf(ServiceReport $serviceReport)
    {
        // Load relationships
        $serviceReport->load('client');
        $company = Company::first();
        
        // Generate PDF using your template
        $pdf = Pdf::loadView('pdf.service_report', [
            'serviceReport' => $serviceReport,
            'company' => $company
        ])->setPaper('a4', 'portrait')->setOptions([
            'defaultFont' => 'sans-serif',
        ]);

        $sanitizedReportNumber = str_replace(['/', '\\'], '-', $serviceReport->service_report_number);

        $filename = "service-report-{$sanitizedReportNumber}.pdf";

        return $pdf->download($filename);
    }
}
