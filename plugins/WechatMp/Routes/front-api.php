<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\WechatMp\Controllers\Front\WechatMpController;

Route::post('/wechat/mini/login', [WechatMpController::class, 'miniLogin'])->name('wechat_mp.mini.login');
Route::get('/wechat/jssdk', [WechatMpController::class, 'jsSdk'])->name('wechat_mp.jssdk');
