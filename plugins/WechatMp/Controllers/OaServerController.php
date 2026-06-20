<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\WechatMp\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Plugin\WechatMp\Services\WechatMpService;

class OaServerController extends Controller
{
    /**
     * 公众号服务端入口（消息/事件接收，关键词自动回复）。
     */
    public function serve()
    {
        try {
            $service = WechatMpService::getInstance();
            $app     = $service->oaApp();
            $server  = $app->getServer();

            // 文本消息关键词自动回复
            $server->with(function ($message, \Closure $next) use ($service) {
                if (($message->MsgType ?? '') === 'text') {
                    $reply = $service->matchAutoReply((string) ($message->Content ?? ''));
                    if ($reply !== null && $reply !== '') {
                        return $reply;
                    }
                }

                return $next($message);
            });

            return $server->serve();
        } catch (\Throwable $e) {
            Log::error('wechat_mp.oa.serve.failed', ['error' => $e->getMessage()]);

            return response('success');
        }
    }
}
