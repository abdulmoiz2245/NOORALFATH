<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('local_purchase_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->string('lpo_number')->unique();
            $table->date('issue_date');
            $table->string('trn_number')->nullable();
            $table->string('subject')->nullable();
            $table->text('terms')->nullable();
            $table->text('description')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total_tax', 12, 2)->default(0);
            $table->decimal('total_before_tax', 12, 2)->default(0);
            $table->decimal('total_after_tax', 12, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('local_purchase_order_items');
        Schema::dropIfExists('local_purchase_order');
    }
};
