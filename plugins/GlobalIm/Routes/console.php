<?php
use Illuminate\Support\Facades\Route;
use Plugin\GlobalIm\Controllers\Console\GlobalImController;

Route::get('/global-im', [GlobalImController::class, 'index'])->name('global_im.index');
Route::post('/global-im/send', [GlobalImController::class, 'send'])->name('global_im.send');
