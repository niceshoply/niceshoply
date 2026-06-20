<?php
use Illuminate\Support\Facades\Route;
use Plugin\ProductFeed\Controllers\Console\ProductFeedController;

Route::get('/product-feed', [ProductFeedController::class, 'index'])->name('product_feed.index');
Route::post('/product-feed/generate', [ProductFeedController::class, 'generate'])->name('product_feed.generate');
