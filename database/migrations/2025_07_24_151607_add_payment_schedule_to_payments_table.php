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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('invoice_payment_schedule_id')
                  ->nullable()
                  ->constrained('invoice_payment_schedules')
                  ->onDelete('cascade');
            $table->string('status')->default('completed');
            $table->index(['invoice_payment_schedule_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['invoice_payment_schedule_id']);
            $table->dropColumn(['invoice_payment_schedule_id', 'status']);
        });
    }
};
