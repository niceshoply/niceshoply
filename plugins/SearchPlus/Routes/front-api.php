<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\SearchPlus\Controllers\Front\SearchController;

Route::get('/search-plus', [SearchController::class, 'search'])->name('search_plus.search');
Route::get('/search-plus/hotwords', [SearchController::class, 'hotWords'])->name('search_plus.hotwords');
Route::get('/search-plus/suggest', [SearchController::class, 'suggest'])->name('search_plus.suggest');
