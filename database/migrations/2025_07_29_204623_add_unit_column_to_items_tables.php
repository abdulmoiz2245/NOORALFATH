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
        // Add unit column to quotation_items table
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->string('unit')->default('pcs')->after('description');
        });

        // Add unit column to invoice_items table
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('unit')->default('pcs')->after('description');
        });

        // Add unit column to local_purchase_order_items table
        Schema::table('local_purchase_order_items', function (Blueprint $table) {
            $table->string('unit')->default('pcs')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove unit column from quotation_items table
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn('unit');
        });

        // Remove unit column from invoice_items table
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('unit');
        });

        // Remove unit column from local_purchase_order_items table
        Schema::table('local_purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
