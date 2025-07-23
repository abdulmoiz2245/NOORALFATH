<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/app.php';

// Email routes for quotations and invoices
Route::middleware(['auth'])->group(function () {
    Route::post('/quotations/{quotation}/email', [App\Http\Controllers\QuotationController::class, 'sendEmail'])->name('quotations.email');
    Route::post('/invoices/{invoice}/email', [App\Http\Controllers\InvoiceController::class, 'sendEmail'])->name('invoices.email');
});
