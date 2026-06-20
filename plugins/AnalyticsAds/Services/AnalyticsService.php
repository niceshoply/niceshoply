<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\AnalyticsAds\Services;

class AnalyticsService
{
    public static function getInstance(): static
    {
        return new static;
    }

    protected function setting(string $key): string
    {
        return trim((string) plugin_setting('analytics_ads', $key, ''));
    }

    /**
     * 注入到 <head> 的统计/像素代码。
     */
    public function renderHead(): string
    {
        if (! (bool) plugin_setting('analytics_ads', 'enabled', true)) {
            return '';
        }

        $out = [];

        if (($ga = $this->setting('ga4_id')) !== '') {
            $gaId = e($ga);
            $out[] = <<<HTML
<script async src="https://www.googletagmanager.com/gtag/js?id={$gaId}"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{$gaId}');</script>
HTML;
        }

        if (($baidu = $this->setting('baidu_id')) !== '') {
            $baiduId = e($baidu);
            $out[] = <<<HTML
<script>var _hmt=_hmt||[];(function(){var hm=document.createElement("script");hm.src="https://hm.baidu.com/hm.js?{$baiduId}";var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(hm,s);})();</script>
HTML;
        }

        if (($meta = $this->setting('meta_pixel_id')) !== '') {
            $metaId = e($meta);
            $out[] = <<<HTML
<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{$metaId}');fbq('track','PageView');</script>
HTML;
        }

        if (($tt = $this->setting('tiktok_pixel_id')) !== '') {
            $ttId = e($tt);
            $out[] = <<<HTML
<script>!function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"];ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{};ttq._i[e]=[];ttq._i[e]._u=i;ttq._t=ttq._t||{};ttq._t[e]=+new Date;ttq._o=ttq._o||{};ttq._o[e]=n||{};var o=d.createElement("script");o.type="text/javascript";o.async=!0;o.src=i+"?sdkid="+e+"&lib="+t;var a=d.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};ttq.load('{$ttId}');ttq.page();}(window,document,'ttq');</script>
HTML;
        }

        if (($custom = $this->setting('custom_head')) !== '') {
            $out[] = $custom; // 原样输出，管理员自负其责
        }

        return $out ? "\n".implode("\n", $out)."\n" : '';
    }

    /**
     * 下单成功页购买转化事件。
     *
     * @param  float   $amount
     * @param  string  $currency
     * @param  string  $orderNumber
     */
    public function renderPurchaseEvent(float $amount, string $currency, string $orderNumber): string
    {
        if (! (bool) plugin_setting('analytics_ads', 'enabled', true)) {
            return '';
        }

        $amount      = round($amount, 2);
        $currency    = e($currency);
        $orderNumber = e($orderNumber);
        $events      = [];

        if ($this->setting('ga4_id') !== '') {
            $events[] = "if(window.gtag){gtag('event','purchase',{transaction_id:'{$orderNumber}',value:{$amount},currency:'{$currency}'});}";
        }
        if ($this->setting('meta_pixel_id') !== '') {
            $events[] = "if(window.fbq){fbq('track','Purchase',{value:{$amount},currency:'{$currency}'});}";
        }
        if ($this->setting('tiktok_pixel_id') !== '') {
            $events[] = "if(window.ttq){ttq.track('CompletePayment',{value:{$amount},currency:'{$currency}'});}";
        }

        return $events ? "<script>\n".implode("\n", $events)."\n</script>" : '';
    }
}
