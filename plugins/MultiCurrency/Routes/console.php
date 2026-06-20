<?php
use Illuminate\Support\Facades\Route;
use Plugin\MultiCurrency\Controllers\Console\MultiCurrencyController;

Route::get('/multi-currency', [MultiCurrencyController::class, 'index'])->name('multi_currency.index');
Route::post('/multi-currency/refresh', [MultiCurrencyController::class, 'refresh'])->name('multi_currency.refresh');
Route::post('/multi-currency/region', [MultiCurrencyController::class, 'storeRegion'])->name('multi_currency.region.store');
