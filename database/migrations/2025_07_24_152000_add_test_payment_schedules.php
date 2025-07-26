<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This migration adds test payment schedules to existing invoices
        // Run this only once to test the payment system
        
        $invoices = \App\Models\Invoice::whereDoesntHave('paymentSchedules')->take(3)->get();
        
        foreach ($invoices as $invoice) {
            // Create 2 payment schedules for each invoice
            \App\Models\InvoicePaymentSchedule::create([
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total_amount * 0.5, // 50% first payment
                'due_date' => now()->addDays(30),
                'status' => 'pending',
                'description' => 'First payment - 50%'
            ]);
            
            \App\Models\InvoicePaymentSchedule::create([
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total_amount * 0.5, // 50% second payment
                'due_date' => now()->addDays(60),
                'status' => 'pending',
                'description' => 'Second payment - 50%'
            ]);
        }
    }

    public function down(): void
    {
        // Remove test payment schedules
        \App\Models\InvoicePaymentSchedule::where('description', 'like', '%payment -%')->delete();
    }
};
