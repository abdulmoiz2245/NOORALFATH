<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->integer('payment_number'); // 1, 2, 3, etc.
            $table->string('description')->nullable(); // e.g., "First Payment (30%)"
            $table->decimal('percentage', 5, 2)->nullable(); // e.g., 30.00 for 30%
            $table->decimal('amount', 10, 2); // Fixed amount to be paid
            $table->date('due_date');
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['invoice_id', 'payment_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payment_schedules');
    }
};
