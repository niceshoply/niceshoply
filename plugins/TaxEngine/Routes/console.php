<?php
use Illuminate\Support\Facades\Route;
use Plugin\TaxEngine\Controllers\Console\TaxEngineController;

Route::get('/tax-engine', [TaxEngineController::class, 'index'])->name('tax_engine.index');
Route::post('/tax-engine', [TaxEngineController::class, 'store'])->name('tax_engine.store');
Route::delete('/tax-engine/{id}', [TaxEngineController::class, 'destroy'])->name('tax_engine.destroy');
