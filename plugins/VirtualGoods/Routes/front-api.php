<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\VirtualGoods\Controllers\Front\VirtualGoodsController;

Route::get('/virtual-goods/my', [VirtualGoodsController::class, 'index'])->name('virtual_goods.my');
