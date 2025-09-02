<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Company;
use App\Models\Project;
use App\Models\Product;
use App\Models\Payment;
use App\Models\InvoicePaymentSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Invoice::with(['client', 'project', 'paymentSchedules'])
            ->withCount(['items']);

        // Apply filters
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('client', function ($clientQuery) use ($request) {
                      $clientQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $invoices = $query->latest()
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
                    'payment_schedules' => $invoice->paymentSchedules->map(function ($schedule) {
                        return [
                            'id' => $schedule->id,
                            'description' => $schedule->description,
                            'amount' => $schedule->amount,
                            'due_date' => $schedule->due_date,
                            'status' => $schedule->status,
                            'paid_amount' => $schedule->paid_amount,
                            'paid_date' => $schedule->paid_date,
                        ];
                    }),
                ];
            });
        $totalReceivedAmount = 0;
        $pendingAmount = 0;
        foreach ($invoices as $invoice) {
            foreach ($invoice['payment_schedules'] as $schedule) {
                $totalReceivedAmount += $schedule['paid_amount'];
            }
        }
        // Calculate stats from database
        $stats = [
            'total' => Invoice::sum('total_amount'),
            'pending' => Invoice::where('status', 'pending')->count(),
            'overdue' => Invoice::where('status', 'overdue')->count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'paid_amount' => $totalReceivedAmount,
            'pending_amount' => Invoice::where('status', 'pending')->sum('total_amount'),
            'overdue_amount' => Invoice::where('status', 'overdue')->sum('total_amount'),
            'draft' => Invoice::where('status', 'draft')->count(),
        ];

        return Inertia::render('invoices/Index', [
            'invoices' => $invoices,
            'stats' => $stats
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
        $units = $this->getPredefinedUnits();
        return Inertia::render('invoices/Create', [
            'clients' => $clients,
            'projects' => $projects,
            'products' => $products,
            'units' => $units,
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
            'po_number' => 'nullable|string|max:255',
            'invoice_number' => 'required|string|unique:invoices',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'status' => 'required|in:draft,pending,paid,overdue,cancelled',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.unit' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.vat_rate' => 'required|numeric|min:0|max:100',
            'items.*.total_price' => 'required|numeric|min:0',
            'items.*.amount' => 'required|numeric|min:0',
            'payment_schedules' => 'nullable|array|min:1',
            'payment_schedules.*.description' => 'required_with:payment_schedules|string',
            'payment_schedules.*.percentage' => 'nullable|numeric|min:0|max:100',
            'payment_schedules.*.amount' => 'nullable|numeric|min:0',
            'payment_schedules.*.due_date' => 'required_with:payment_schedules|date',
            'payment_schedules.*.attachments' => 'nullable|array',
            'payment_schedules.*.attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $totalAmountWithoutTax = 0;
        $totalAmountWithTax = 0;
        $totalTaxAmount = 0;

       
        

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $itemSubtotal = $item['quantity'] * $item['unit_price'];
            $itemVat = $itemSubtotal * ($item['vat_rate'] / 100);
            $subtotal += $itemSubtotal + $itemVat;

            $totalAmountWithoutTax += $itemSubtotal;
            $totalAmountWithTax += $itemSubtotal + $itemVat;
            $totalTaxAmount += $itemVat;
            
        }

       

        $taxRate = $validated['tax_rate'] ?? 0;
        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;

        // dd($validated['payment_schedules']);

        $invoice = Invoice::create([
            'client_id' => $validated['client_id'],
            'po_number' => $validated['po_number'],
            'project_id' => $validated['project_id'],
            'invoice_number' => $validated['invoice_number'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'status' => $validated['status'],
            'notes' => $validated['notes'],
            'tax_rate' => $taxRate,
            'subtotal' => $totalAmountWithoutTax,
            'tax_amount' => $totalTaxAmount,
            'total_amount' => $totalAmountWithTax,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'paid_amount' => 0,
            'balance_due' => $totalAmountWithTax
        ]);

        // Create invoice items
        foreach ($validated['items'] as $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'description' => $item['description'],
                'unit' => $item['unit'] ?? 'pcs',
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'vat_rate' => $item['vat_rate'],
                'total_price' => $item['amount'],
                'total_price_w_tax' => $item['total_price'],
            ]);
        }

        // Create payment schedules if provided
        if (isset($validated['payment_schedules']) && !empty($validated['payment_schedules'])) {
            foreach ($validated['payment_schedules'] as $index => $schedule) {
                $amount = $schedule['amount'] ?? ($totalAmount * ($schedule['percentage'] / 100));
                $attachments = [];
                if (isset($schedule['attachments']) && is_array($schedule['attachments'])) {
                    foreach ($schedule['attachments'] as $file) {
                        if ($file instanceof \Illuminate\Http\UploadedFile) {
                            $attachments[] = $file->store('payment_attachments', 'public');
                        }
                    }
                }
                $invoice->paymentSchedules()->create([
                    'payment_number' => $index + 1,
                    'description' => $schedule['description'],
                    'percentage' => $schedule['percentage'] ?? null,
                    'amount' => $amount,
                    'due_date' => $schedule['due_date'],
                    'status' => 'pending',
                    'attachments' => $attachments,
                ]);
            }
        } else {
            // Create default single payment schedule
            $invoice->paymentSchedules()->create([
                'payment_number' => 1,
                'description' => 'Full Payment',
                'percentage' => 100,
                'amount' => $totalAmount,
                'due_date' => $validated['due_date'],
                'status' => 'pending',
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
        $invoice->load(['client', 'project', 'items.product', 'paymentSchedules.payments']);
        
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
        $units = $this->getPredefinedUnits();
        
        $invoice->load(['items', 'paymentSchedules']);
        
        return Inertia::render('invoices/Edit', [
            'invoice' => $invoice,
            'clients' => $clients,
            'projects' => $projects,
            'products' => $products,
            'units' => $units
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
            'po_number' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'status' => 'required|in:draft,pending,paid,overdue,cancelled',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'discount_amount' => 'nullable|numeric|min:0',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.unit' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.vat_rate' => 'required|numeric|min:0|max:100',
            'items.*.total_price' => 'required|numeric|min:0',
            'items.*.amount' => 'required|numeric|min:0',
            'payment_schedules' => 'nullable|array|min:1',
            'payment_schedules.*.description' => 'required_with:payment_schedules|string',
            'payment_schedules.*.percentage' => 'nullable|numeric|min:0|max:100',
            'payment_schedules.*.amount' => 'nullable|numeric|min:0',
            'payment_schedules.*.due_date' => 'required_with:payment_schedules|date',
            'payment_schedules.*.attachments' => 'nullable|array',
            'payment_schedules.*.attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);


        $subtotal = 0;
        $totalAmountWithoutTax = 0;
        $totalAmountWithTax = 0;
        $totalTaxAmount = 0;
        foreach ($validated['items'] as $item) {
            $itemSubtotal = $item['quantity'] * $item['unit_price'];
            $itemVat = $itemSubtotal * ($item['vat_rate'] / 100);
            $subtotal += $itemSubtotal + $itemVat;

            $totalAmountWithoutTax += $itemSubtotal;
            $totalAmountWithTax += $itemSubtotal + $itemVat;
            $totalTaxAmount += $itemVat;
        }

        $taxRate = $validated['tax_rate'] ?? 0;
        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;

        $invoice->update([
            'client_id' => $validated['client_id'],
            'project_id' => $validated['project_id'],
            'po_number' => $validated['po_number'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'status' => $validated['status'],
            'notes' => $validated['notes'],
            'tax_rate' => $taxRate,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'subtotal' => $totalAmountWithoutTax,
            'tax_amount' => $totalTaxAmount,
            'total_amount' => $totalAmountWithTax,
        ]);

        // Recalculate balance due based on existing payments
        $totalPaidAmount = $invoice->payments()->sum('amount');
        $balanceDue = $totalAmountWithTax - $totalPaidAmount;
        $invoice->update([
            'balance_due' => max(0, $balanceDue)
        ]);

        // Delete existing items and create new ones
        $invoice->items()->delete();
        $totalAmountWithoutTax = 0;
        $totalAmountWithTax = 0;
        $totalTaxAmount = 0;

        foreach ($validated['items'] as $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'description' => $item['description'],
                'unit' => $item['unit'] ?? 'pcs',
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'vat_rate' => $item['vat_rate'],
                'total_price' => $item['amount'],
                'total_price_w_tax' => $item['total_price'],
            ]);
            
        }

        // Update payment schedules
        $invoice->paymentSchedules()->delete();
        if (isset($validated['payment_schedules']) && !empty($validated['payment_schedules'])) {
            foreach ($validated['payment_schedules'] as $index => $schedule) {
                $amount = $schedule['amount'] ?? ($totalAmount * ($schedule['percentage'] / 100));
                $attachments = [];
                if (isset($schedule['attachments']) && is_array($schedule['attachments'])) {
                    foreach ($schedule['attachments'] as $file) {
                        if ($file instanceof \Illuminate\Http\UploadedFile) {
                            $attachments[] = $file->store('payment_attachments', 'public');
                        } elseif (is_string($file)) {
                            $attachments[] = $file;
                        }
                    }
                }
                $invoice->paymentSchedules()->create([
                    'payment_number' => $index + 1,
                    'description' => $schedule['description'],
                    'percentage' => $schedule['percentage'] ?? null,
                    'amount' => $amount,
                    'due_date' => $schedule['due_date'],
                    'status' => 'pending',
                    'attachments' => $attachments,
                ]);
            }
        } else {
            // Create default single payment schedule
            $invoice->paymentSchedules()->create([
                'payment_number' => 1,
                'description' => 'Full Payment',
                'percentage' => 100,
                'amount' => $totalAmount,
                'due_date' => $validated['due_date'],
                'status' => 'pending',
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
        $year = date('Y'); // not used in format but can be useful if needed later
        $month = date('m');
        
        $lastInvoice = Invoice::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        // Format: NAFT/INV/MM/000001
        return sprintf('NAFT/INV/%s/%06d', $month, $lastInvoice + 1);
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
    public function generatePdf(Invoice $invoice, Request $request)
    {
        $paymentScheduleId = $request->query('payment_schedule_id');
        $invoice->load(['client', 'items.product', 'paymentSchedules']);
        
        if ($paymentScheduleId) {
            $paymentSchedule = $invoice->paymentSchedules()->findOrFail($paymentScheduleId);
            // TODO: Implement payment-specific PDF generation
            return redirect()->route('invoices.show', $invoice)
                ->with('message', 'Payment-specific PDF generated successfully.');
        }
        
        // TODO: Implement full invoice PDF generation
        return redirect()->route('invoices.show', $invoice)
            ->with('message', 'Full invoice PDF generated successfully.');
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

    /**
     * Update the status of the invoice.
     */
    public function updateStatus(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,pending,paid,overdue,cancelled'
        ]);

        $invoice->update([
            'status' => $validated['status']
        ]);

        return redirect()->back()->with('message', 'Invoice status updated successfully');
    }

    /**
     * Download PDF for a specific payment (Payment Receipt)
     */
    public function downloadPaymentPdf(Invoice $invoice, Payment $payment)
    {
        // Load relationships
        $invoice->load(['client', 'project', 'items.product', 'paymentSchedules.payments']);
        
        // Verify the payment belongs to this invoice
        $belongsToInvoice = $invoice->paymentSchedules()->whereHas('payments', function($query) use ($payment) {
            $query->where('id', $payment->id);
        })->exists();
        
        if (!$belongsToInvoice) {
            abort(404, 'Payment not found for this invoice');
        }
        
        // Generate PDF using your template
        $pdf = Pdf::loadView('pdf.sheduled_invoice', [
            'invoice' => $invoice,
            'payment' => $payment,
            'type' => 'payment_receipt'
        ])->setPaper('a4', 'portrait')->setOptions([
            'margin-top' => 20,
            'margin-bottom' => 20,
            'margin-left' => 20,
            'margin-right' => 20,
            'defaultFont' => 'sans-serif',
        ]);
        
        $sanitizedInvoiceNumber = str_replace(['/', '\\'], '-', $invoice->invoice_number);

        $filename = "invoice-{$sanitizedInvoiceNumber}-payment-{$payment->id}.pdf";
        
        return $pdf->download($filename);
    }

    /**
     * Download PDF for a payment schedule (Invoice for unpaid amount)
     */
    public function downloadSchedulePdf(Invoice $invoice, InvoicePaymentSchedule $schedule)
    {
        // Load relationships
        $invoice->load(['client', 'project', 'items.product', 'paymentSchedules.payments']);
        $company = Company::first();
        // Verify the schedule belongs to this invoice
        if ($schedule->invoice_id !== $invoice->id) {
            abort(404, 'Payment schedule not found for this invoice');
        }
        $bankDetails = json_decode($company->bank_details, true);
        $company->bank_details = $bankDetails;
        // Generate PDF using your template
        $pdf = Pdf::loadView('pdf.sheduled_invoice', [
            'invoice' => $invoice,
            'schedule' => $schedule,
            'type' => 'invoice',
            'company' => $company
        ])->setPaper('a4', 'portrait')->setOptions([
            'defaultFont' => 'sans-serif',
      
        ]);
        
        $filename = "invoice-{$invoice->invoice_number}-schedule-{$schedule->id}.pdf";
        
        return $pdf->download($filename);
    }

    /**
     * Get filter data for invoices
     */
    public function getFilterData()
    {
        $clients = Client::select('id', 'name')
            ->whereHas('invoices')
            ->orderBy('name')
            ->get();

        $statuses = Invoice::select('status')
            ->distinct()
            ->whereNotNull('status')
            ->pluck('status')
            ->map(function ($status) {
                return [
                    'value' => $status,
                    'label' => ucfirst(str_replace('_', ' ', $status))
                ];
            });

        return response()->json([
            'clients' => $clients,
            'statuses' => $statuses
        ]);
    }

    private function getPredefinedUnits(): array
    {
        return [
            'pcs' => 'Pieces',
            'kg' => 'Kilogram',
            'gm' => 'Gram',
            'ltr' => 'Liter',
            'ml' => 'Milliliter',
            'mtr' => 'Meter',
            'cm' => 'Centimeter',
            'mm' => 'Millimeter',
            'sq_mtr' => 'Square Meter',
            'cu_mtr' => 'Cubic Meter',
            'ft' => 'Feet',
            'inch' => 'Inch',
            'box' => 'Box',
            'pack' => 'Pack',
            'set' => 'Set',
            'pair' => 'Pair',
            'doz' => 'Dozen',
            'roll' => 'Roll',
            'bundle' => 'Bundle',
            'lot' => 'Lot',
            'unit' => 'Unit',
            'hour' => 'Hour',
            'day' => 'Day',
            'month' => 'Month',
            'year' => 'Year',
        ];
    }
}
