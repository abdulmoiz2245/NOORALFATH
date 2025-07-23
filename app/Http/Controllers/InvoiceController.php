<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Project;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $invoices = Invoice::with(['client', 'project'])
            ->withCount(['items'])
            ->latest()
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client' => $invoice->client->name,
                    'client_id' => $invoice->client_id,
                    'project' => $invoice->project ? $invoice->project->name : null,
                    'project_id' => $invoice->project_id,
                    'status' => $invoice->status,
                    'issue_date' => $invoice->issue_date,
                    'due_date' => $invoice->due_date,
                    'subtotal' => $invoice->subtotal,
                    'tax_amount' => $invoice->tax_amount,
                    'total_amount' => $invoice->total_amount,
                    'items_count' => $invoice->items_count,
                    'created_at' => $invoice->created_at,
                ];
            });

        return Inertia::render('invoices/Index', [
            'invoices' => $invoices
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
        return Inertia::render('invoices/Create', [
            'clients' => $clients,
            'projects' => $projects,
            'products' => $products,
            'nextInvoiceNumber' => $this->generateInvoiceNumber()
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
            'invoice_number' => 'required|string|unique:invoices',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'status' => 'required|in:draft,pending,paid,overdue,cancelled',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $taxRate = $validated['tax_rate'] ?? 0;
        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;

        $invoice = Invoice::create([
            'client_id' => $validated['client_id'],
            'project_id' => $validated['project_id'],
            'invoice_number' => $validated['invoice_number'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'status' => $validated['status'],
            'notes' => $validated['notes'],
            'terms' => $validated['terms'],
            'tax_rate' => $taxRate,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);

        // Create invoice items
        foreach ($validated['items'] as $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('invoices.index')
            ->with('message', 'Invoice created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): Response
    {
        $invoice->load(['client', 'project', 'items.product']);
        
        return Inertia::render('invoices/Show', [
            'invoice' => $invoice
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice): Response
    {
        $clients = Client::select('id', 'name')->get();
        $projects = Project::with('client')->select('id', 'name', 'client_id')->get();
        $products = Product::select('id', 'name', 'price')->get();
        
        $invoice->load(['items']);
        
        return Inertia::render('invoices/Edit', [
            'invoice' => $invoice,
            'clients' => $clients,
            'projects' => $projects,
            'products' => $products
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'status' => 'required|in:draft,pending,paid,overdue,cancelled',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $taxRate = $validated['tax_rate'] ?? 0;
        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;

        $invoice->update([
            'client_id' => $validated['client_id'],
            'project_id' => $validated['project_id'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'status' => $validated['status'],
            'notes' => $validated['notes'],
            'terms' => $validated['terms'],
            'tax_rate' => $taxRate,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);

        // Delete existing items and create new ones
        $invoice->items()->delete();
        foreach ($validated['items'] as $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('invoices.index')
            ->with('message', 'Invoice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('message', 'Invoice deleted successfully.');
    }

    /**
     * Generate a new invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastInvoice = Invoice::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();
        
        return sprintf('INV-%s%s-%04d', $year, $month, $lastInvoice + 1);
    }

    /**
     * Duplicate an invoice
     */
    public function duplicate(Invoice $invoice)
    {
        $newInvoice = $invoice->replicate();
        $newInvoice->invoice_number = $this->generateInvoiceNumber();
        $newInvoice->status = 'draft';
        $newInvoice->issue_date = now();
        $newInvoice->due_date = now()->addDays(30);
        $newInvoice->save();

        // Duplicate invoice items
        foreach ($invoice->items as $item) {
            $newItem = $item->replicate();
            $newItem->invoice_id = $newInvoice->id;
            $newItem->save();
        }

        return redirect()->route('invoices.edit', $newInvoice)
            ->with('message', 'Invoice duplicated successfully.');
    }

    /**
     * Send invoice via email
     */
    public function send(Invoice $invoice)
    {
        // TODO: Implement email sending functionality
        return redirect()->route('invoices.show', $invoice)
            ->with('message', 'Invoice sent successfully.');
    }

    /**
     * Generate PDF for invoice
     */
    public function generatePdf(Invoice $invoice)
    {
        // TODO: Implement PDF generation
        return redirect()->route('invoices.show', $invoice)
            ->with('message', 'PDF generated successfully.');
    }

    /**
     * Send invoice via email
     */
    public function sendEmail(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'attach_pdf' => 'boolean',
        ]);

        try {
            $invoice->load(['client', 'items.product']);

            $mail = new \App\Mail\InvoiceMail(
                invoice: $invoice,
                customMessage: $validated['message'],
                attachPdf: $validated['attach_pdf'] ?? true
            );

            \Illuminate\Support\Facades\Mail::to($validated['to'])
                ->send($mail);

            // Update invoice status to sent if it was draft
            if ($invoice->status === 'draft') {
                $invoice->update(['status' => 'sent']);
            }

            return redirect()->back()
                ->with('message', 'Invoice email sent successfully to ' . $validated['to']);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Invoice email failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['email' => 'Failed to send email: ' . $e->getMessage()]);
        }
    }
}
