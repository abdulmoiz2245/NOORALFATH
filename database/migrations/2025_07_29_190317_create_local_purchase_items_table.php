<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('local_purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('local_purchase_order_id')->constrained('local_purchase_order')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->string('description');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('total_price_before_tax', 12, 2);
            $table->decimal('total_price_after_tax', 12, 2);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('local_purchase_order_items');
    }
};
