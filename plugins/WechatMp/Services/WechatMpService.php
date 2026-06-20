<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\WechatMp\Services;

use Exception;
use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Services\JwtTokenService;
use Plugin\WechatMp\Models\AutoReply;
use Plugin\WechatMp\Models\WechatUser;

/**
 * 微信公众号/小程序服务（基于 EasyWeChat v6）。
 *
 * 注意：需通过 composer 安装 w7corp/easywechat，并在插件配置填入凭证。
 */
class WechatMpService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public static function ready(): bool
    {
        return class_exists(\EasyWeChat\MiniApp\Application::class)
            || class_exists(\EasyWeChat\OfficialAccount\Application::class);
    }

    /**
     * 小程序 Application。
     *
     * @throws Exception
     */
    public function miniApp()
    {
        $this->assertReady();

        return new \EasyWeChat\MiniApp\Application([
            'app_id' => (string) plugin_setting('wechat_mp', 'mini_app_id'),
            'secret' => (string) plugin_setting('wechat_mp', 'mini_secret'),
        ]);
    }

    /**
     * 公众号 Application。
     *
     * @throws Exception
     */
    public function oaApp()
    {
        $this->assertReady();

        return new \EasyWeChat\OfficialAccount\Application([
            'app_id'  => (string) plugin_setting('wechat_mp', 'oa_app_id'),
            'secret'  => (string) plugin_setting('wechat_mp', 'oa_secret'),
            'token'   => (string) plugin_setting('wechat_mp', 'oa_token'),
            'aes_key' => (string) plugin_setting('wechat_mp', 'oa_aes_key'),
        ]);
    }

    /**
     * 小程序登录：code -> openid -> 关联/创建会员 -> 签发 token。
     *
     * @return array{access_token:string, token_type:string, expires_in:int}
     * @throws Exception
     */
    public function miniLogin(string $code, ?string $deviceName = null): array
    {
        if ($code === '') {
            throw new RuntimeException(__('WechatMp::common.need_code'));
        }

        $app     = $this->miniApp();
        $session = $app->getUtils()->codeToSession($code);
        $openid  = (string) ($session['openid'] ?? '');
        $unionid = (string) ($session['unionid'] ?? '');

        if ($openid === '') {
            throw new RuntimeException(__('WechatMp::common.login_failed'));
        }

        $customer = $this->resolveCustomer($openid, $unionid, 'mini');

        return app(JwtTokenService::class)->issueToken($customer, 'customer_api', $deviceName);
    }

    /**
     * 通过 openid 关联会员，没有则创建占位会员。
     */
    protected function resolveCustomer(string $openid, string $unionid, string $source): Customer
    {
        return DB::transaction(function () use ($openid, $unionid, $source) {
            /** @var WechatUser $map */
            $map = WechatUser::query()->firstOrNew(['openid' => $openid, 'source' => $source]);

            if ($map->customer_id && ($c = Customer::query()->find($map->customer_id))) {
                if ($unionid && ! $map->unionid) {
                    $map->unionid = $unionid;
                    $map->save();
                }

                return $c;
            }

            $customer = Customer::query()->create([
                'email'    => 'wx_'.Str::random(16).'@wechat.local',
                'password' => bcrypt(Str::random(32)),
                'name'     => 'WeChat User',
                'active'   => true,
                'from'     => 'wechat_'.$source,
            ]);

            $map->customer_id = $customer->id;
            $map->unionid     = $unionid ?: null;
            $map->save();

            return $customer;
        });
    }

    /**
     * 根据收到的文本匹配自动回复内容；无匹配返回 null。
     */
    public function matchAutoReply(string $text): ?string
    {
        $text = trim($text);

        $replies = AutoReply::query()->where('is_active', true)->orderBy('sort')->orderBy('id')->get();

        foreach ($replies as $reply) {
            if ($reply->match_type === 'equal' && $reply->keyword !== null && $text === $reply->keyword) {
                return $reply->content;
            }
            if ($reply->match_type === 'contains' && $reply->keyword && Str::contains($text, $reply->keyword)) {
                return $reply->content;
            }
        }

        // 兜底默认回复
        $default = $replies->firstWhere('match_type', 'default');

        return $default?->content;
    }

    /**
     * JS-SDK 配置。
     *
     * @throws Exception
     */
    public function jsSdkConfig(string $url, array $apis = []): array
    {
        $app = $this->oaApp();

        return $app->getUtils()->buildJsSdkConfig($url, $apis ?: ['updateAppMessageShareData', 'updateTimelineShareData']);
    }

    /**
     * @throws Exception
     */
    protected function assertReady(): void
    {
        if (! self::ready()) {
            throw new Exception('EasyWeChat is not installed. Run: composer require w7corp/easywechat');
        }
    }
}
