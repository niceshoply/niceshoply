<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\SmartRecommend\Controllers\Front\RecommendController;

Route::post('/recommend/view', [RecommendController::class, 'view'])->name('recommend.view');
Route::get('/recommend/recently-viewed', [RecommendController::class, 'recentlyViewed'])->name('recommend.recently_viewed');
Route::get('/recommend/for-you', [RecommendController::class, 'forYou'])->name('recommend.for_you');
Route::get('/recommend/hot', [RecommendController::class, 'hot'])->name('recommend.hot');
Route::get('/recommend/viewed-also-viewed/{productId}', [RecommendController::class, 'viewedAlsoViewed'])->whereNumber('productId')->name('recommend.viewed_also_viewed');
Route::get('/recommend/bought-together/{productId}', [RecommendController::class, 'boughtTogether'])->whereNumber('productId')->name('recommend.bought_together');
