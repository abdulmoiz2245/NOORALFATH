<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display the main reports page
     */
    public function index(): Response
    {
        // Get dashboard data for charts
        $dashboardData = $this->getDashboardData();
        
        return Inertia::render('reports/Index', [
            'dashboardData' => $dashboardData
        ]);
    }

    /**
     * Get dashboard data for charts
     */
    private function getDashboardData(): array
    {
        // Monthly revenue for the last 6 months
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');
            $revenue = Payment::whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount');
            
            $monthlyRevenue[] = [
                'month' => $monthName,
                'revenue' => (float) $revenue
            ];
        }

        // Invoice status distribution
        $invoiceStatusData = [
            ['status' => 'Paid', 'count' => Invoice::where('status', 'paid')->count()],
            ['status' => 'Pending', 'count' => Invoice::where('status', 'pending')->count()],
            ['status' => 'Overdue', 'count' => Invoice::where('status', 'overdue')->count()],
            ['status' => 'Draft', 'count' => Invoice::where('status', 'draft')->count()],
        ];

        // Top 5 clients by total payments
        $topClients = Client::select('clients.name')
            ->selectRaw('SUM(payments.amount) as total_paid')
            ->join('invoices', 'clients.id', '=', 'invoices.client_id')
            ->join('invoice_payment_schedules', 'invoices.id', '=', 'invoice_payment_schedules.invoice_id')
            ->join('payments', 'invoice_payment_schedules.id', '=', 'payments.invoice_payment_schedule_id')
            ->groupBy('clients.id', 'clients.name')
            ->orderByDesc('total_paid')
            ->limit(5)
            ->get()
            ->map(fn($client) => [
                'client' => $client->name,
                'amount' => (float) $client->total_paid
            ]);

        // Recent payments trend (last 7 days)
        $recentPayments = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayName = $date->format('M j');
            $amount = Payment::whereDate('payment_date', $date->toDateString())
                ->sum('amount');
            
            $recentPayments[] = [
                'day' => $dayName,
                'amount' => (float) $amount
            ];
        }

        return [
            'monthlyRevenue' => $monthlyRevenue,
            'invoiceStatus' => $invoiceStatusData,
            'topClients' => $topClients,
            'recentPayments' => $recentPayments,
            'totalStats' => [
                'totalInvoices' => Invoice::count(),
                'totalClients' => Client::count(),
                'totalRevenue' => Payment::sum('amount'),
                'pendingAmount' => Invoice::where('status', '!=', 'paid')->sum('total_amount')
            ]
        ];
    }

    /**
     * Display invoice reports with payment information
     */
    public function invoices(Request $request)
    {
        $query = Invoice::with(['client', 'paymentSchedules.payments'])
            ->withCount(['items']);

        // Apply date filters
        if ($request->filled('date_from')) {
            $query->where('issue_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('issue_date', '<=', $request->date_to);
        }

        // Apply client filter
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest('issue_date')
            ->get()
            ->map(function ($invoice) {
                // Calculate total payments received for this invoice
                $totalReceived = 0;
                $lastPaymentDate = null;
                $paymentCount = 0;

                foreach ($invoice->paymentSchedules as $schedule) {
                    foreach ($schedule->payments as $payment) {
                        $totalReceived += $payment->amount;
                        $paymentCount++;
                        if (!$lastPaymentDate || $payment->payment_date > $lastPaymentDate) {
                            $lastPaymentDate = $payment->payment_date;
                        }
                    }
                }

                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client_name' => $invoice->client->name,
                    'client_id' => $invoice->client_id,
                    'issue_date' => $invoice->issue_date,
                    'due_date' => $invoice->due_date,
                    'status' => $invoice->status,
                    'total_amount' => floatval($invoice->total_amount),
                    'amount_received' => $totalReceived,
                    'amount_remaining' => floatval($invoice->total_amount) - $totalReceived,
                    'payment_percentage' => $invoice->total_amount > 0 ? round(($totalReceived / floatval($invoice->total_amount)) * 100, 2) : 0,
                    'last_payment_date' => $lastPaymentDate,
                    'payment_count' => $paymentCount,
                    'items_count' => $invoice->items_count,
                ];
            });

        // Handle export
        if ($request->has('export')) {
            return $this->exportInvoicesReport($invoices, $request->export);
        }

        // Calculate summary statistics
        $totalInvoiceAmount = $invoices->sum('total_amount');
        $totalAmountReceived = $invoices->sum('amount_received');
        $totalAmountRemaining = $invoices->sum('amount_remaining');

        $stats = [
            'total_invoices' => $invoices->count(),
            'total_invoice_amount' => $totalInvoiceAmount,
            'total_amount_received' => $totalAmountReceived,
            'total_amount_remaining' => $totalAmountRemaining,
            'overall_collection_rate' => $totalInvoiceAmount > 0 ? round(($totalAmountReceived / $totalInvoiceAmount) * 100, 2) : 0,
            'fully_paid_invoices' => $invoices->where('payment_percentage', 100)->count(),
            'partially_paid_invoices' => $invoices->where('payment_percentage', '>', 0)->where('payment_percentage', '<', 100)->count(),
            'unpaid_invoices' => $invoices->where('payment_percentage', 0)->count(),
        ];

        // Get filter data
        $clients = Client::select('id', 'name')->orderBy('name')->get();
        $statuses = ['draft', 'pending', 'paid', 'overdue'];

        return Inertia::render('reports/Invoices', [
            'invoices' => $invoices->values(),
            'stats' => $stats,
            'clients' => $clients,
            'statuses' => $statuses,
            'filters' => $request->only(['date_from', 'date_to', 'client_id', 'status'])
        ]);
    }

    /**
     * Display client reports
     */
    public function clients(Request $request)
    {
        $query = Client::withCount(['invoices', 'projects'])
            ->with(['invoices' => function ($q) {
                $q->with('paymentSchedules.payments');
            }]);

        // Apply date filters for invoices
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $query->whereHas('invoices', function ($q) use ($request) {
                if ($request->filled('date_from')) {
                    $q->where('issue_date', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $q->where('issue_date', '<=', $request->date_to);
                }
            });
        }

        $clients = $query->get()->map(function ($client) {
            $totalInvoiced = 0;
            $totalReceived = 0;
            $lastInvoiceDate = null;
            $lastPaymentDate = null;

            foreach ($client->invoices as $invoice) {
                $totalInvoiced += floatval($invoice->total_amount);
                
                if (!$lastInvoiceDate || $invoice->issue_date > $lastInvoiceDate) {
                    $lastInvoiceDate = $invoice->issue_date;
                }

                foreach ($invoice->paymentSchedules as $schedule) {
                    foreach ($schedule->payments as $payment) {
                        $totalReceived += $payment->amount;
                        if (!$lastPaymentDate || $payment->payment_date > $lastPaymentDate) {
                            $lastPaymentDate = $payment->payment_date;
                        }
                    }
                }
            }

            return [
                'id' => $client->id,
                'name' => $client->name,
                'company_name' => $client->company_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'invoices_count' => $client->invoices_count,
                'projects_count' => $client->projects_count,
                'total_invoiced' => $totalInvoiced,
                'total_received' => $totalReceived,
                'outstanding_amount' => $totalInvoiced - $totalReceived,
                'collection_rate' => $totalInvoiced > 0 ? round(($totalReceived / $totalInvoiced) * 100, 2) : 0,
                'last_invoice_date' => $lastInvoiceDate,
                'last_payment_date' => $lastPaymentDate,
            ];
        })->sortByDesc('total_invoiced');

        // Handle export
        if ($request->has('export')) {
            return $this->exportClientsReport($clients, $request->export);
        }

        return Inertia::render('reports/Clients', [
            'clients' => $clients->values(),
            'filters' => $request->only(['date_from', 'date_to'])
        ]);
    }

    /**
     * Display payment reports
     */
    public function payments(Request $request)
    {
        $query = Payment::with(['invoice.client', 'paymentSchedule']);

        // Apply date filters
        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        // Apply client filter
        if ($request->filled('client_id')) {
            $query->whereHas('invoice', function ($q) use ($request) {
                $q->where('client_id', $request->client_id);
            });
        }

        // Apply payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->latest('payment_date')->get()->map(function ($payment) {
            return [
                'id' => $payment->id,
                'invoice_number' => $payment->invoice->invoice_number,
                'client_name' => $payment->invoice->client->name,
                'client_id' => $payment->invoice->client_id,
                'amount' => floatval($payment->amount),
                'payment_date' => $payment->payment_date,
                'payment_method' => $payment->payment_method,
                'reference_number' => $payment->reference_number,
                'status' => $payment->status,
                'notes' => $payment->notes,
            ];
        });

        // Handle export
        if ($request->has('export')) {
            return $this->exportPaymentsReport($payments, $request->export);
        }

        // Calculate stats
        $stats = [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'average_payment' => $payments->count() > 0 ? $payments->avg('amount') : 0,
            'this_month_total' => $payments->where('payment_date', '>=', Carbon::now()->startOfMonth())->sum('amount'),
        ];

        $clients = Client::select('id', 'name')->orderBy('name')->get();
        $paymentMethods = Payment::distinct()->pluck('payment_method')->filter();

        return Inertia::render('reports/Payments', [
            'payments' => $payments->values(),
            'stats' => $stats,
            'clients' => $clients,
            'payment_methods' => $paymentMethods,
            'filters' => $request->only(['date_from', 'date_to', 'client_id', 'payment_method'])
        ]);
    }

    /**
     * Display financial summary
     */
    public function summary(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfYear()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        // Invoice summary
        $invoiceStats = [
            'total_invoices' => Invoice::whereBetween('issue_date', [$dateFrom, $dateTo])->count(),
            'total_invoiced' => Invoice::whereBetween('issue_date', [$dateFrom, $dateTo])->sum('total_amount'),
            'paid_invoices' => Invoice::whereBetween('issue_date', [$dateFrom, $dateTo])->where('status', 'paid')->count(),
            'pending_invoices' => Invoice::whereBetween('issue_date', [$dateFrom, $dateTo])->where('status', 'pending')->count(),
            'overdue_invoices' => Invoice::whereBetween('issue_date', [$dateFrom, $dateTo])->where('status', 'overdue')->count(),
        ];

        // Payment summary
        $paymentStats = [
            'total_payments' => Payment::whereBetween('payment_date', [$dateFrom, $dateTo])->count(),
            'total_received' => Payment::whereBetween('payment_date', [$dateFrom, $dateTo])->sum('amount'),
            'average_payment' => Payment::whereBetween('payment_date', [$dateFrom, $dateTo])->avg('amount') ?: 0,
        ];

        // Monthly breakdown
        $monthlyData = [];
        $start = Carbon::parse($dateFrom)->startOfMonth();
        $end = Carbon::parse($dateTo)->endOfMonth();

        while ($start <= $end) {
            $monthStart = $start->copy()->startOfMonth();
            $monthEnd = $start->copy()->endOfMonth();
            
            $monthlyData[] = [
                'month' => $start->format('Y-m'),
                'month_name' => $start->format('M Y'),
                'invoiced' => Invoice::whereBetween('issue_date', [$monthStart, $monthEnd])->sum('total_amount'),
                'received' => Payment::whereBetween('payment_date', [$monthStart, $monthEnd])->sum('amount'),
                'invoices_count' => Invoice::whereBetween('issue_date', [$monthStart, $monthEnd])->count(),
                'payments_count' => Payment::whereBetween('payment_date', [$monthStart, $monthEnd])->count(),
            ];
            
            $start->addMonth();
        }

        // Handle export
        if ($request->has('export')) {
            return $this->exportSummaryReport([
                'invoice_stats' => $invoiceStats,
                'payment_stats' => $paymentStats,
                'monthly_data' => $monthlyData,
                'date_range' => ['from' => $dateFrom, 'to' => $dateTo]
            ], $request->export);
        }

        return Inertia::render('reports/Summary', [
            'invoice_stats' => $invoiceStats,
            'payment_stats' => $paymentStats,
            'monthly_data' => $monthlyData,
            'filters' => ['date_from' => $dateFrom, 'date_to' => $dateTo]
        ]);
    }

    /**
     * Export invoice reports
     */
    private function exportInvoicesReport($invoices, $format): StreamedResponse
    {
        $filename = 'invoice-report-' . date('Y-m-d-H-i-s');
        
        if ($format === 'csv') {
            return $this->exportCSV($invoices, $filename, [
                'Invoice Number' => 'invoice_number',
                'Client Name' => 'client_name',
                'Issue Date' => 'issue_date',
                'Due Date' => 'due_date',
                'Status' => 'status',
                'Total Amount' => 'total_amount',
                'Amount Received' => 'amount_received',
                'Amount Remaining' => 'amount_remaining',
                'Payment Percentage' => 'payment_percentage',
                'Last Payment Date' => 'last_payment_date',
                'Payment Count' => 'payment_count',
                'Items Count' => 'items_count',
            ]);
        }
        
        // Default to Excel format
        return $this->exportExcel($invoices, $filename, [
            'Invoice Number' => 'invoice_number',
            'Client Name' => 'client_name',
            'Issue Date' => 'issue_date',
            'Due Date' => 'due_date',
            'Status' => 'status',
            'Total Amount' => 'total_amount',
            'Amount Received' => 'amount_received',
            'Amount Remaining' => 'amount_remaining',
            'Payment %' => 'payment_percentage',
            'Last Payment' => 'last_payment_date',
            'Payments' => 'payment_count',
            'Items' => 'items_count',
        ]);
    }

    /**
     * Export client reports
     */
    private function exportClientsReport($clients, $format): StreamedResponse
    {
        $filename = 'client-report-' . date('Y-m-d-H-i-s');
        
        $columns = [
            'Client Name' => 'name',
            'Company' => 'company_name',
            'Email' => 'email',
            'Phone' => 'phone',
            'Invoices Count' => 'invoices_count',
            'Projects Count' => 'projects_count',
            'Total Invoiced' => 'total_invoiced',
            'Total Received' => 'total_received',
            'Outstanding Amount' => 'outstanding_amount',
            'Collection Rate %' => 'collection_rate',
            'Last Invoice Date' => 'last_invoice_date',
            'Last Payment Date' => 'last_payment_date',
        ];
        
        if ($format === 'csv') {
            return $this->exportCSV($clients, $filename, $columns);
        }
        
        return $this->exportExcel($clients, $filename, $columns);
    }

    /**
     * Export payment reports
     */
    private function exportPaymentsReport($payments, $format): StreamedResponse
    {
        $filename = 'payment-report-' . date('Y-m-d-H-i-s');
        
        $columns = [
            'Invoice Number' => 'invoice_number',
            'Client Name' => 'client_name',
            'Amount' => 'amount',
            'Payment Date' => 'payment_date',
            'Payment Method' => 'payment_method',
            'Reference Number' => 'reference_number',
            'Status' => 'status',
            'Notes' => 'notes',
        ];
        
        if ($format === 'csv') {
            return $this->exportCSV($payments, $filename, $columns);
        }
        
        return $this->exportExcel($payments, $filename, $columns);
    }

    /**
     * Export summary reports
     */
    private function exportSummaryReport($data, $format): StreamedResponse
    {
        $filename = 'financial-summary-' . date('Y-m-d-H-i-s');
        
        // Flatten the data structure for export
        $exportData = collect();
        
        // Add summary stats
        $exportData->push([
            'Type' => 'Invoice Statistics',
            'Metric' => 'Total Invoices',
            'Value' => $data['invoice_stats']['total_invoices'],
            'Period' => $data['date_range']['from'] . ' to ' . $data['date_range']['to']
        ]);
        
        $exportData->push([
            'Type' => 'Invoice Statistics',
            'Metric' => 'Total Invoiced Amount',
            'Value' => $data['invoice_stats']['total_invoiced'],
            'Period' => $data['date_range']['from'] . ' to ' . $data['date_range']['to']
        ]);
        
        $exportData->push([
            'Type' => 'Payment Statistics',
            'Metric' => 'Total Payments',
            'Value' => $data['payment_stats']['total_payments'],
            'Period' => $data['date_range']['from'] . ' to ' . $data['date_range']['to']
        ]);
        
        $exportData->push([
            'Type' => 'Payment Statistics',
            'Metric' => 'Total Received Amount',
            'Value' => $data['payment_stats']['total_received'],
            'Period' => $data['date_range']['from'] . ' to ' . $data['date_range']['to']
        ]);
        
        // Add monthly data
        foreach ($data['monthly_data'] as $month) {
            $exportData->push([
                'Type' => 'Monthly Data',
                'Metric' => 'Invoiced - ' . $month['month_name'],
                'Value' => $month['invoiced'],
                'Period' => $month['month']
            ]);
            
            $exportData->push([
                'Type' => 'Monthly Data',
                'Metric' => 'Received - ' . $month['month_name'],
                'Value' => $month['received'],
                'Period' => $month['month']
            ]);
        }
        
        $columns = [
            'Type' => 'Type',
            'Metric' => 'Metric',
            'Value' => 'Value',
            'Period' => 'Period',
        ];
        
        if ($format === 'csv') {
            return $this->exportCSV($exportData, $filename, $columns);
        }
        
        return $this->exportExcel($exportData, $filename, $columns);
    }

    /**
     * Export data as CSV
     */
    private function exportCSV($data, $filename, $columns): StreamedResponse
    {
        return new StreamedResponse(function () use ($data, $columns) {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for proper encoding in Excel
            fwrite($handle, "\xEF\xBB\xBF");
            
            // Add headers
            fputcsv($handle, array_keys($columns));
            
            // Add data rows
            foreach ($data as $row) {
                $csvRow = [];
                foreach ($columns as $column) {
                    $value = is_array($row) ? ($row[$column] ?? '') : $row->$column ?? '';
                    $csvRow[] = $value;
                }
                fputcsv($handle, $csvRow);
            }
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ]);
    }

    /**
     * Export data as Excel (simplified HTML table format)
     */
    private function exportExcel($data, $filename, $columns): StreamedResponse
    {
        return new StreamedResponse(function () use ($data, $columns) {
            echo '<table border="1">';
            
            // Headers
            echo '<tr>';
            foreach (array_keys($columns) as $header) {
                echo '<th>' . htmlspecialchars($header) . '</th>';
            }
            echo '</tr>';
            
            // Data rows
            foreach ($data as $row) {
                echo '<tr>';
                foreach ($columns as $column) {
                    $value = is_array($row) ? ($row[$column] ?? '') : $row->$column ?? '';
                    echo '<td>' . htmlspecialchars($value) . '</td>';
                }
                echo '</tr>';
            }
            
            echo '</table>';
        }, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.xls"',
        ]);
    }

    /**
     * Display tax reports
     */
    public function tax(Request $request): Response
    {
        // Default to last 3 months if no date range provided
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $startDate = $request->get('start_date', Carbon::parse($endDate)->subMonths(3)->toDateString());

        // Get all payments within the date range
        $payments = Payment::with(['invoice.items', 'invoice.client'])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->get();

        // Get all deductible expenses within the date range
        $expenses = \App\Models\Expense::whereBetween('expense_date', [$startDate, $endDate])
            // ->where('is_billable', false) // Only non-billable expenses are tax deductible
            ->get();

        // Calculate tax collected from payments
        $taxCollectedData = $this->calculateTaxFromPayments($payments);
        
        // Calculate total deductible expenses
        $totalExpenses = $expenses->sum('amount');

        // Calculate net tax liability
        $netTaxLiability = $taxCollectedData['total_tax'] - ($totalExpenses * 0.15); // Assuming 15% VAT on expenses

        return Inertia::render('reports/Tax', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'taxCollectedData' => $taxCollectedData,
            'expenses' => $expenses,
            'totalExpenses' => $totalExpenses,
            'netTaxLiability' => max(0, $netTaxLiability), // Tax liability cannot be negative
            'summary' => [
                'total_revenue' => $taxCollectedData['total_revenue'],
                'total_tax_collected' => $taxCollectedData['total_tax'],
                'total_expenses' => $totalExpenses,
                'tax_on_expenses' => $totalExpenses * 0.15,
                'net_tax_liability' => max(0, $netTaxLiability),
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate,
                    'days' => Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1
                ]
            ]
        ]);
    }

    private function calculateTaxFromPayments($payments)
    {
        $totalRevenue = 0;
        $totalTax = 0;
        $invoiceBreakdown = [];

        foreach ($payments as $payment) {
            $invoice = $payment->invoice;
            
            // Calculate what percentage of the invoice this payment represents
            $invoiceTotal = $invoice->total_amount;
            $paymentPercentage = $invoiceTotal > 0 ? ($payment->amount / $invoiceTotal) : 0;

            // Calculate tax for this payment based on invoice items
            $invoiceTax = 0;
            $invoiceRevenue = $payment->amount;

            foreach ($invoice->items as $item) {
                $itemTax = $item->tax_amount ?? 0;
                $proportionalTax = $itemTax * $paymentPercentage;
                $invoiceTax += $proportionalTax;
            }

            $totalRevenue += $invoiceRevenue;
            $totalTax += $invoiceTax;

            // Store breakdown for detailed view
            $invoiceKey = $invoice->id;
            if (!isset($invoiceBreakdown[$invoiceKey])) {
                $invoiceBreakdown[$invoiceKey] = [
                    'invoice' => $invoice,
                    'payments' => [],
                    'total_paid' => 0,
                    'total_tax_collected' => 0
                ];
            }

            $invoiceBreakdown[$invoiceKey]['payments'][] = [
                'payment' => $payment,
                'tax_amount' => $invoiceTax,
                'percentage_of_invoice' => $paymentPercentage * 100
            ];
            $invoiceBreakdown[$invoiceKey]['total_paid'] += $invoiceRevenue;
            $invoiceBreakdown[$invoiceKey]['total_tax_collected'] += $invoiceTax;
        }

        return [
            'total_revenue' => $totalRevenue,
            'total_tax' => $totalTax,
            'invoice_breakdown' => array_values($invoiceBreakdown)
        ];
    }
}
