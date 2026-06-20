<?php
use Illuminate\Support\Facades\Route;
use Plugin\TaxEngine\Controllers\Front\TaxController;

Route::get('/tax/estimate', [TaxController::class, 'estimate'])->name('tax_engine.estimate');
Route::post('/tax/vat/validate', [TaxController::class, 'validateVat'])->name('tax_engine.vat.validate');
