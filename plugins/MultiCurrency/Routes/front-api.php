<?php
use Illuminate\Support\Facades\Route;
use Plugin\MultiCurrency\Controllers\Front\CurrencyController;

Route::get('/currency/list', [CurrencyController::class, 'list'])->name('multi_currency.list');
Route::post('/currency/switch', [CurrencyController::class, 'switch'])->name('multi_currency.switch');
Route::get('/currency/convert', [CurrencyController::class, 'convert'])->name('multi_currency.convert');
