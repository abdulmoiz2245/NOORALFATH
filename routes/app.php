<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // Company Profile Routes
    Route::get('/company', [CompanyController::class, 'index'])->name('company.index');
    Route::get('/company/create', [CompanyController::class, 'create'])->name('company.create');
    Route::post('/company', [CompanyController::class, 'store'])->name('company.store');
    Route::get('/company/{id}/edit', [CompanyController::class, 'edit'])->name('company.edit');
    Route::post('/company/{id}', [CompanyController::class, 'update'])->name('company.update');
    Route::patch('/company/{id}', [CompanyController::class, 'update'])->name('company.patch');

    // Client Management Routes
    Route::resource('clients', ClientController::class);
    
    // Project Management Routes
    Route::resource('projects', ProjectController::class);
    
    // Invoice Management Routes
    Route::resource('invoices', InvoiceController::class)->except([
        'update'
    ]);
    Route::post('invoices/{invoice}/', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::get('invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoices.duplicate');
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'generatePdf'])->name('invoices.pdf');
    Route::patch('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.update-status');
    Route::get('api/invoices/filter-data', [InvoiceController::class, 'getFilterData'])->name('invoices.filter-data');
    
    // PDF Download Routes
    Route::get('invoices/{invoice}/payments/{payment}/download-pdf', [InvoiceController::class, 'downloadPaymentPdf'])->name('invoices.payments.download-pdf');
    Route::get('invoices/{invoice}/schedules/{schedule}/download-pdf', [InvoiceController::class, 'downloadSchedulePdf'])->name('invoices.schedules.download-pdf');

    Route::get('quotations/{quotation}/download-pdf', [QuotationController::class, 'downloadPdf'])->name('quotations.download-pdf');

    // Quotation Management Routes
    Route::resource('quotations', QuotationController::class);
    Route::get('quotations/{quotation}/duplicate', [QuotationController::class, 'duplicate'])->name('quotations.duplicate');
    Route::post('quotations/{quotation}/send', [QuotationController::class, 'send'])->name('quotations.send');
    Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'generatePdf'])->name('quotations.pdf');
    Route::post('quotations/{quotation}/convert-to-invoice', [QuotationController::class, 'convertToInvoice'])->name('quotations.convert-to-invoice');
    
    // Product Management Routes
    Route::resource('products', ProductController::class);
    
    // Vendor Management Routes
    Route::resource('vendors', VendorController::class);
    
    // Expense Management Routes
    Route::resource('expenses', ExpenseController::class);
    
    // Payment Management Routes
    Route::resource('payments', PaymentController::class);
    Route::get('payment-schedules/{schedule}/pay', [PaymentController::class, 'createForSchedule'])->name('payment-schedules.pay');
    
    // Report Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/clients', [ReportController::class, 'clients'])->name('clients');
        Route::get('/clients/{client}', [ReportController::class, 'clientDetail'])->name('clients.detail');
        Route::get('/projects', [ReportController::class, 'projects'])->name('projects');
        Route::get('/invoices', [ReportController::class, 'invoices'])->name('invoices');
        Route::get('/payments', [ReportController::class, 'payments'])->name('payments');
        Route::get('/expenses', [ReportController::class, 'expenses'])->name('expenses');
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/summary', [ReportController::class, 'summary'])->name('summary');
    });
    
    // API Routes for AJAX requests
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('clients/search', [ClientController::class, 'search'])->name('clients.search');
        Route::get('products/search', [ProductController::class, 'search'])->name('products.search');
        Route::get('invoices/recurring', [InvoiceController::class, 'recurring'])->name('invoices.recurring');
        Route::get('invoices/due', [InvoiceController::class, 'due'])->name('invoices.due');
    });
});
