<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Company;
use App\Models\Project;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $quotations = Quotation::with(['client', 'project'])
            ->withCount(['items'])
            ->latest()
            ->get()
            ->map(function ($quotation) {
                return [
                    'id' => $quotation->id,
                    'quotation_number' => $quotation->quotation_number,
                    'client' => $quotation->client->name,
                    'client_id' => $quotation->client_id,
                    'project' => $quotation->project ? $quotation->project->name : null,
                    'project_id' => $quotation->project_id,
                    'status' => $quotation->status,
                    'issue_date' => $quotation->issue_date,
                    'valid_until' => $quotation->valid_until,
                    'subtotal' => $quotation->subtotal,
                    'tax_amount' => $quotation->tax_amount,
                    'total_amount' => $quotation->total_amount,
                    'items_count' => $quotation->items_count,
                    'created_at' => $quotation->created_at,
                ];
            });

        return Inertia::render('quotations/Index', [
            'quotations' => $quotations
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $clients = Client::select('id', 'name')->get();
        $projects = Project::with('client')->select('id', 'name', 'client_id')->get();
        $products = Product::select('id', 'name', 'price')->get();
        
        return Inertia::render('quotations/Create', [
            'clients' => $clients,
            'projects' => $projects,
            'products' => $products,
            'nextQuotationNumber' => $this->generateQuotationNumber()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'quotation_number' => 'required|string|unique:quotations',
            // 'issue_date' => 'required|date',
            'valid_until' => 'required|date|after:issue_date',
            'status' => 'nullable|in:draft,sent,accepted,rejected,expired',
            'title' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_amount' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100'
        ]);


        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $taxRate = 5;
        $taxAmount = 0;
        $totalAmountBeforeTax = 0;
        $totalAmountWithTax = 0;

        foreach ($validated['items'] as $item) {
            $totalAmountBeforeTax += $item['quantity'] * $item['unit_price'];
            $taxAmount += ($item['quantity'] * $item['unit_price']) * ($taxRate / 100);
        }
        $totalAmountWithTax = $totalAmountBeforeTax + $taxAmount;

        $quotation = Quotation::create([
            'client_id' => $validated['client_id'],
            // 'project_id' => $validated['project_id'],
            'quotation_number' => $validated['quotation_number'],
            'issue_date' => $validated['issue_date'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'valid_until' => $validated['valid_until'],
            // 'status' => $validated['status'],
            'notes' => $validated['notes'],
            // 'terms' => $validated['terms'],
            'tax_rate' => $taxRate,
            'subtotal' => $totalAmountBeforeTax,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmountWithTax,
        ]);

        // Create quotation items
        foreach ($validated['items'] as $item) {
            $quotation->items()->create([
                'product_id' => $item['product_id'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_amount'],
                'tax_rate' => $item['tax_rate'], 
            ]);
        }

        return redirect()->route('quotations.index')
            ->with('message', 'Quotation created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Quotation $quotation): Response
    {
        $quotation->load(['client', 'project', 'items.product']);
        
        return Inertia::render('quotations/Show', [
            'quotation' => $quotation
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quotation $quotation): Response
    {
        $clients = Client::select('id', 'name')->get();
        $projects = Project::with('client')->select('id', 'name', 'client_id')->get();
        $products = Product::select('id', 'name', 'price')->get();
        
        $quotation->load(['items']);
        
        return Inertia::render('quotations/Edit', [
            'quotation' => $quotation,
            'clients' => $clients,
            'projects' => $projects,
            'products' => $products
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'issue_date' => 'required|date',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'valid_until' => 'required|date|after:issue_date',
            'notes' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_amount' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100'
        ]);
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $taxRate = $validated['tax_rate'] ?? 0;
        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;

        $taxRate = 5;
        $taxAmount = 0;
        $totalAmountBeforeTax = 0;
        $totalAmountWithTax = 0;

        foreach ($validated['items'] as $item) {
            $totalAmountBeforeTax += $item['quantity'] * $item['unit_price'];
            $taxAmount += ($item['quantity'] * $item['unit_price']) * ($taxRate / 100);
        }
        $totalAmountWithTax = $totalAmountBeforeTax + $taxAmount;

        $quotation->update([
            'client_id' => $validated['client_id'],
            // 'project_id' => $validated['project_id'],
            'issue_date' => $validated['issue_date'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'valid_until' => $validated['valid_until'],
            // 'status' => $validated['status'],
            'notes' => $validated['notes'],
            // 'terms' => $validated['terms'],
            'tax_rate' => $taxRate,
            'subtotal' => $totalAmountBeforeTax,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmountWithTax,
        ]);

        // Delete existing items and create new ones
        $quotation->items()->delete();
        foreach ($validated['items'] as $item) {
            $quotation->items()->create([
                'product_id' => $item['product_id'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_amount'],
                'tax_rate' => $item['tax_rate'], 
            ]);
        }

        return redirect()->route('quotations.index')
            ->with('message', 'Quotation updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quotation $quotation)
    {
        $quotation->delete();

        return redirect()->route('quotations.index')
            ->with('message', 'Quotation deleted successfully.');
    }

    /**
     * Generate a new quotation number
     */
    private function generateQuotationNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastQuotation = Quotation::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();
        
        return sprintf('QUO-%s%s-%04d', $year, $month, $lastQuotation + 1);
    }

    /**
     * Duplicate a quotation
     */
    public function duplicate(Quotation $quotation)
    {
        $newQuotation = $quotation->replicate();
        $newQuotation->quotation_number = $this->generateQuotationNumber();
        $newQuotation->status = 'draft';
        $newQuotation->issue_date = now();
        $newQuotation->valid_until = now()->addDays(30);
        $newQuotation->save();

        // Duplicate quotation items
        foreach ($quotation->items as $item) {
            $newItem = $item->replicate();
            $newItem->quotation_id = $newQuotation->id;
            $newItem->save();
        }

        return redirect()->route('quotations.edit', $newQuotation)
            ->with('message', 'Quotation duplicated successfully.');
    }

    /**
     * Send quotation via email
     */
    public function send(Quotation $quotation)
    {
        // TODO: Implement email sending functionality
        return redirect()->route('quotations.show', $quotation)
            ->with('message', 'Quotation sent successfully.');
    }

    /**
     * Generate PDF for quotation
     */
    public function generatePdf(Quotation $quotation)
    {
        // TODO: Implement PDF generation
        return redirect()->route('quotations.show', $quotation)
            ->with('message', 'PDF generated successfully.');
    }

    /**
     * Convert quotation to invoice
     */
    public function convertToInvoice(Quotation $quotation)
    {
        // Generate new invoice number
        $year = date('Y');
        $month = date('m');
        $lastInvoice = Invoice::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();
        
        $invoiceNumber = sprintf('INV-%s%s-%04d', $year, $month, $lastInvoice + 1);

        // Create new invoice from quotation
        $invoice = Invoice::create([
            'client_id' => $quotation->client_id,
            'project_id' => $quotation->project_id,
            'invoice_number' => $invoiceNumber,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'pending',
            'notes' => $quotation->notes,
            'terms' => $quotation->terms,
            'tax_rate' => $quotation->tax_rate,
            'subtotal' => $quotation->subtotal,
            'tax_amount' => $quotation->tax_amount,
            'total_amount' => $quotation->total_amount,
        ]);

        // Copy quotation items to invoice
        foreach ($quotation->items as $item) {
            $invoice->items()->create([
                'product_id' => $item->product_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ]);
        }

        // Update quotation status
        $quotation->update(['status' => 'accepted']);

        return redirect()->route('invoices.show', $invoice)
            ->with('message', 'Quotation converted to invoice successfully.');
    }

    /**
     * Send quotation via email
     */
    public function sendEmail(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'attach_pdf' => 'boolean',
        ]);

        try {
            $quotation->load(['client', 'items.product']);

            $mail = new \App\Mail\QuotationMail(
                quotation: $quotation,
                customMessage: $validated['message'],
                attachPdf: $validated['attach_pdf'] ?? true
            );

            \Illuminate\Support\Facades\Mail::to($validated['to'])
                ->send($mail);

            // Update quotation status to sent if it was draft
            if ($quotation->status === 'draft') {
                $quotation->update(['status' => 'sent']);
            }

            return redirect()->back()
                ->with('message', 'Quotation email sent successfully to ' . $validated['to']);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Quotation email failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['email' => 'Failed to send email: ' . $e->getMessage()]);
        }
    }

    public function downloadPdf(Quotation $quotation)
    {
        // Load relationships
        $quotation->load(['client', 'project', 'items']);
        $company = Company::first();
       
        $bankDetails = json_decode($company->bank_details, true);
        $company->bank_details = $bankDetails;
        // Generate PDF using your template
        $pdf = Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
            'company' => $company
        ])->setPaper('a4', 'portrait')->setOptions([
            'defaultFont' => 'sans-serif',
      
        ]);

        $filename = "quotation-{$quotation->quotation_number}.pdf";

        return $pdf->download($filename);
    }
}
