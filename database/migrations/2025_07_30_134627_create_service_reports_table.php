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
        Schema::create('service_reports', function (Blueprint $table) {
            $table->id();
            $table->string('service_report_number')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->date('service_date');
            $table->boolean('ac_work')->default(false);
            $table->boolean('plumbing_work')->default(false);
            $table->boolean('paint_work')->default(false);
            $table->boolean('electrical_work')->default(false);
            $table->boolean('civil_work')->default(false);
            $table->text('job_details');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_reports');
    }
};
