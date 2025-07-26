<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\InvoicePaymentSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = Payment::with(['invoice', 'paymentSchedule'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('payment/Index', [
            'payments' => $payments
        ]);
    }

    /**
     * Show payment form for a specific payment schedule.
     */
    public function createForSchedule(InvoicePaymentSchedule $schedule)
    {
        $schedule->load('invoice');
        
        // Calculate remaining amount
        $paidAmount = $schedule->payments()->sum('amount');
        $remainingAmount = $schedule->amount - $paidAmount;
        if ($remainingAmount <= 0) {
            return redirect()->back()->with('error', 'This payment schedule is already fully paid.');
        }

        return Inertia::render('payment/Create', [
            'schedule' => $schedule,
            'remainingAmount' => $remainingAmount,
            'paidAmount' => $paidAmount
        ]);
    }

    /**
     * Store a newly created payment against a payment schedule.
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_payment_schedule_id' => 'required|exists:invoice_payment_schedules,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120' // 5MB max
        ]);

        $schedule = InvoicePaymentSchedule::findOrFail($request->invoice_payment_schedule_id);
        
        // Calculate remaining amount
        $paidAmount = $schedule->payments()->sum('amount');
        $remainingAmount = $schedule->amount - $paidAmount;
        if ($request->amount > $remainingAmount) {
            return redirect()->back()->withErrors([
                'amount' => 'Payment amount cannot exceed remaining amount of $' . number_format($remainingAmount, 2)
            ]);
        }

        DB::transaction(function () use ($request, $schedule) {
            // Handle receipt upload
            $receiptPath = null;
            if ($request->hasFile('receipt')) {
                $receiptPath = $request->file('receipt')->store('payment-receipts', 'public');
            }

            // Create the payment
            $payment = Payment::create([
                'invoice_id' => $schedule->invoice_id,
                'invoice_payment_schedule_id' => $schedule->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
                'status' => 'completed',
                'receipt_path' => $receiptPath
            ]);

            // Update schedule status
            $totalPaid = $schedule->payments()->sum('amount');
            if ($totalPaid >= $schedule->amount) {
                $schedule->update(['status' => 'paid']);
            } else {
                $schedule->update(['status' => 'partial']);
            }

            // Update invoice status based on all payment schedules
            $invoice = $schedule->invoice;
            $allSchedules = $invoice->paymentSchedules;
            $allPaid = $allSchedules->every(function ($s) {
                return $s->status === 'paid';
            });
            
            if ($allPaid) {
                $invoice->update(['status' => 'paid']);
            } elseif ($allSchedules->contains('status', 'partial') || $allSchedules->contains('status', 'paid')) {
                $invoice->update(['status' => 'partially_paid']);
            }
        });

        return redirect()->route('invoices.show', $schedule->invoice_id)
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        $payment->load(['invoice', 'paymentSchedule']);
        
        return Inertia::render('payment/Show', [
            'payment' => $payment
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        $payment->load(['invoice', 'paymentSchedule']);
        
        return Inertia::render('payment/Edit', [
            'payment' => $payment
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120' // 5MB max
        ]);

        $schedule = $payment->paymentSchedule;
        
        // Calculate available amount for this payment
        $otherPaymentsTotal = $schedule->payments()->where('id', '!=', $payment->id)->sum('amount');
        $maxAmount = $schedule->amount - $otherPaymentsTotal;
        
        if ($request->amount > $maxAmount) {
            return redirect()->back()->withErrors([
                'amount' => 'Payment amount cannot exceed available amount of $' . number_format($maxAmount, 2)
            ]);
        }

        DB::transaction(function () use ($request, $payment, $schedule) {
            // Handle receipt upload
            $receiptPath = $payment->receipt_path; // Keep existing receipt if no new one
            if ($request->hasFile('receipt')) {
                // Delete old receipt if exists
                if ($payment->receipt_path && Storage::disk('public')->exists($payment->receipt_path)) {
                    Storage::disk('public')->delete($payment->receipt_path);
                }
                $receiptPath = $request->file('receipt')->store('payment-receipts', 'public');
            }

            $payment->update([
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
                'receipt_path' => $receiptPath
            ]);

            // Recalculate schedule status
            $totalPaid = $schedule->payments()->sum('amount');
            if ($totalPaid >= $schedule->amount) {
                $schedule->update(['status' => 'paid']);
            } elseif ($totalPaid > 0) {
                $schedule->update(['status' => 'partial']);
            } else {
                $schedule->update(['status' => 'pending']);
            }

            // Update invoice status
            $invoice = $schedule->invoice;
            $allSchedules = $invoice->paymentSchedules;
            $allPaid = $allSchedules->every(function ($s) {
                return $s->status === 'paid';
            });
            
            if ($allPaid) {
                $invoice->update(['status' => 'paid']);
            } elseif ($allSchedules->contains('status', 'partial') || $allSchedules->contains('status', 'paid')) {
                $invoice->update(['status' => 'partially_paid']);
            } else {
                $invoice->update(['status' => 'sent']);
            }
        });

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        $schedule = $payment->paymentSchedule;
        $invoice = $payment->invoice;
        
        DB::transaction(function () use ($payment, $schedule, $invoice) {
            $payment->delete();
            
            // Recalculate schedule status
            $totalPaid = $schedule->payments()->sum('amount');
            if ($totalPaid >= $schedule->amount) {
                $schedule->update(['status' => 'paid']);
            } elseif ($totalPaid > 0) {
                $schedule->update(['status' => 'partial']);
            } else {
                $schedule->update(['status' => 'pending']);
            }

            // Update invoice status
            $allSchedules = $invoice->paymentSchedules;
            $allPaid = $allSchedules->every(function ($s) {
                return $s->status === 'paid';
            });
            
            if ($allPaid) {
                $invoice->update(['status' => 'paid']);
            } elseif ($allSchedules->contains('status', 'partial') || $allSchedules->contains('status', 'paid')) {
                $invoice->update(['status' => 'partially_paid']);
            } else {
                $invoice->update(['status' => 'sent']);
            }
        });

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Payment deleted successfully.');
    }
}
