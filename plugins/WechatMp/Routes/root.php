<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\WechatMp\Controllers\OaServerController;

// 公众号服务器配置 URL（GET 校验 / POST 接收消息），无 locale 前缀
Route::match(['get', 'post'], '/wechat/oa/serve', [OaServerController::class, 'serve'])->name('wechat_mp.oa.serve');
