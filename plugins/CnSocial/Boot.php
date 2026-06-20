<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CnSocial;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

/**
 * 国内社交登录。
 *
 * 依赖（通过插件 composer.json 安装）：
 *   socialiteproviders/weixin、socialiteproviders/qq、socialiteproviders/weibo
 *
 * 注册对应 Socialite 驱动并注入 services 配置，复用核心：
 *   GET /social/{provider}/redirect  与  /social/{provider}/callback
 */
class Boot
{
    /**
     * provider => [SocialiteProviders Extend 类]
     */
    protected array $providers = [
        'weixin' => \SocialiteProviders\Weixin\WeixinExtendSocialite::class,
        'qq'     => \SocialiteProviders\QQ\QqExtendSocialite::class,
        'weibo'  => \SocialiteProviders\Weibo\WeiboExtendSocialite::class,
    ];

    public function init(): void
    {
        $socialiteEvent = \SocialiteProviders\Manager\SocialiteWasCalled::class;
        if (! class_exists($socialiteEvent)) {
            // SDK 未安装，安全降级
            return;
        }

        foreach ($this->providers as $provider => $extendClass) {
            if (! $this->enabled($provider) || ! class_exists($extendClass)) {
                continue;
            }

            // 注册驱动
            Event::listen($socialiteEvent, [$extendClass, 'handle']);

            // 注入配置（核心 callback 使用 services.{provider}）
            Config::set("services.{$provider}", [
                'client_id'     => (string) plugin_setting('cn_social', "{$provider}_app_id"),
                'client_secret' => (string) plugin_setting('cn_social', "{$provider}_app_secret"),
                'redirect'      => $this->callbackUrl($provider),
            ]);
        }
    }

    protected function enabled(string $provider): bool
    {
        return (bool) plugin_setting('cn_social', "{$provider}_enabled", false);
    }

    protected function callbackUrl(string $provider): string
    {
        if (function_exists('front_root_route')) {
            return front_root_route('social.callback', $provider);
        }

        return url("/social/{$provider}/callback");
    }
}
