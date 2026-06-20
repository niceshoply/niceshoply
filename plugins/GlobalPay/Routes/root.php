<?php
use Illuminate\Support\Facades\Route;
use Plugin\GlobalPay\Controllers\GlobalPayController;

Route::match(['get', 'post'], '/callback/global_pay', [GlobalPayController::class, 'notify'])->name('global_pay.notify');
