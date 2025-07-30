<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Expense;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TaxReportController extends Controller
{
    public function index(Request $request)
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
        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->where('is_billable', false) // Only non-billable expenses are tax deductible
            ->get();

        // Calculate tax collected from payments
        $taxCollectedData = $this->calculateTaxFromPayments($payments);
        
        // Calculate total deductible expenses
        $totalExpenses = $expenses->sum('amount');

        // Calculate net tax liability
        $netTaxLiability = $taxCollectedData['total_tax'] - ($totalExpenses * 0.15); // Assuming 15% VAT on expenses

        return Inertia::render('TaxReports/Index', [
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

    public function downloadReport(Request $request)
    {
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $startDate = $request->get('start_date', Carbon::parse($endDate)->subMonths(3)->toDateString());

        // Get the same data as the index method
        $payments = Payment::with(['invoice.items', 'invoice.client'])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->get();

        $expenses = Expense::with(['vendor', 'project'])
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->where('is_billable', false)
            ->get();

        $taxCollectedData = $this->calculateTaxFromPayments($payments);
        $totalExpenses = $expenses->sum('amount');
        $netTaxLiability = max(0, $taxCollectedData['total_tax'] - ($totalExpenses * 0.15));

        $company = \App\Models\Company::first();

        $data = [
            'company' => $company,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'taxCollectedData' => $taxCollectedData,
            'expenses' => $expenses,
            'totalExpenses' => $totalExpenses,
            'netTaxLiability' => $netTaxLiability,
            'summary' => [
                'total_revenue' => $taxCollectedData['total_revenue'],
                'total_tax_collected' => $taxCollectedData['total_tax'],
                'total_expenses' => $totalExpenses,
                'tax_on_expenses' => $totalExpenses * 0.15,
                'net_tax_liability' => $netTaxLiability,
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate,
                    'days' => Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1
                ]
            ]
        ];

        $pdf = Pdf::loadView('tax_report', $data);
        
        $filename = "tax_report_{$startDate}_to_{$endDate}.pdf";
        
        return $pdf->download($filename);
    }
}
