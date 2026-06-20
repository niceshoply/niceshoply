<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Captcha\Services;

use Illuminate\Support\Facades\Http;

class CaptchaService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function provider(): string
    {
        return (string) plugin_setting('captcha', 'provider', 'recaptcha');
    }

    public function siteKey(): string
    {
        return (string) plugin_setting('captcha', 'site_key');
    }

    public function configured(): bool
    {
        return $this->siteKey() !== '' && (string) plugin_setting('captcha', 'secret_key') !== '';
    }

    /**
     * 前台注入的验证脚本 URL。
     */
    public function scriptUrl(): string
    {
        return match ($this->provider()) {
            'turnstile' => 'https://challenges.cloudflare.com/turnstile/v0/api.js',
            'hcaptcha'  => 'https://js.hcaptcha.com/1/api.js',
            default     => 'https://www.google.com/recaptcha/api.js',
        };
    }

    /**
     * 注入到前台 head 的脚本与全局配置。
     */
    public function renderHead(): string
    {
        if (! $this->configured()) {
            return '';
        }

        $cfg = json_encode([
            'provider' => $this->provider(),
            'siteKey'  => $this->siteKey(),
        ], JSON_UNESCAPED_SLASHES);

        return "\n<script>window.NICE_CAPTCHA = {$cfg};</script>\n"
            .'<script src="'.$this->scriptUrl().'" async defer></script>'."\n";
    }

    /**
     * 服务端校验 token。
     */
    public function verify(string $token, string $ip = ''): bool
    {
        if ($token === '') {
            return false;
        }

        $secret = (string) plugin_setting('captcha', 'secret_key');
        if ($secret === '') {
            return false;
        }

        $url = match ($this->provider()) {
            'turnstile' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            'hcaptcha'  => 'https://hcaptcha.com/siteverify',
            default     => 'https://www.google.com/recaptcha/api/siteverify',
        };

        try {
            $response = Http::asForm()->timeout(10)->post($url, array_filter([
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ]));

            return (bool) ($response->json('success') ?? false);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
