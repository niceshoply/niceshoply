<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\LiveChat\Services;

class LiveChatService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function renderWidget(): string
    {
        if (! (bool) plugin_setting('live_chat', 'enabled', true)) {
            return '';
        }

        $provider = (string) plugin_setting('live_chat', 'provider', 'custom');
        $widgetId = trim((string) plugin_setting('live_chat', 'widget_id', ''));

        return match ($provider) {
            'meiqia' => $this->meiqia($widgetId),
            'tawk'   => $this->tawk($widgetId),
            'crisp'  => $this->crisp($widgetId),
            default  => (string) plugin_setting('live_chat', 'custom_code', ''),
        };
    }

    protected function meiqia(string $entId): string
    {
        if ($entId === '') {
            return '';
        }
        $id = e($entId);

        return <<<HTML
<script>(function(a,b,c,d,e,j,s){a[d]=a[d]||function(){(a[d].a=a[d].a||[]).push(arguments)};j=b.createElement(c),s=b.getElementsByTagName(c)[0];j.async=true;j.charset="UTF-8";j.src="https://static.meiqia.com/widget/loader.js";s.parentNode.insertBefore(j,s);})(window,document,"script","_MEIQIA");_MEIQIA('entId','{$id}');</script>
HTML;
    }

    protected function tawk(string $ids): string
    {
        // widget_id 格式：propertyId/widgetId
        $parts      = explode('/', $ids);
        $propertyId = e($parts[0] ?? '');
        $widgetId   = e($parts[1] ?? 'default');
        if ($propertyId === '') {
            return '';
        }

        return <<<HTML
<script>var Tawk_API=Tawk_API||{},Tawk_LoadStart=new Date();(function(){var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];s1.async=true;s1.src='https://embed.tawk.to/{$propertyId}/{$widgetId}';s1.charset='UTF-8';s1.setAttribute('crossorigin','*');s0.parentNode.insertBefore(s1,s0);})();</script>
HTML;
    }

    protected function crisp(string $websiteId): string
    {
        if ($websiteId === '') {
            return '';
        }
        $id = e($websiteId);

        return <<<HTML
<script>window.\$crisp=[];window.CRISP_WEBSITE_ID="{$id}";(function(){var d=document,s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>
HTML;
    }
}
