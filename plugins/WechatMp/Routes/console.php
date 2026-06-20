<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\WechatMp\Controllers\Console\AutoReplyController;

Route::get('/wechat-mp', [AutoReplyController::class, 'index'])->name('wechat_mp.index');
Route::post('/wechat-mp', [AutoReplyController::class, 'store'])->name('wechat_mp.store');
Route::delete('/wechat-mp/{id}', [AutoReplyController::class, 'destroy'])->name('wechat_mp.destroy');
