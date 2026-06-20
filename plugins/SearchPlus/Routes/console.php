<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\SearchPlus\Controllers\Console\SearchPlusController;

Route::get('/search-plus', [SearchPlusController::class, 'index'])->name('search_plus.index');
Route::post('/search-plus/synonym', [SearchPlusController::class, 'storeSynonym'])->name('search_plus.synonym.store');
Route::delete('/search-plus/synonym/{id}', [SearchPlusController::class, 'destroySynonym'])->name('search_plus.synonym.destroy');
Route::post('/search-plus/reindex', [SearchPlusController::class, 'reindex'])->name('search_plus.reindex');
