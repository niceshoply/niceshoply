<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PrivacyConsent\Services;

use Plugin\PrivacyConsent\Models\Consent;

class PrivacyConsentService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (bool) plugin_setting('privacy_consent', 'enabled', true);
    }

    public function logChoice(string $choice, ?string $ip, ?string $ua): Consent
    {
        return Consent::query()->create([
            'choice'     => in_array($choice, ['accept', 'reject'], true) ? $choice : 'accept',
            'ip'         => $ip,
            'user_agent' => $ua ? mb_substr($ua, 0, 255) : null,
            'created_at' => now(),
        ]);
    }

    /**
     * 渲染注入前台的 Cookie 同意横幅。
     */
    public function render(): string
    {
        if (! $this->enabled()) {
            return '';
        }

        $message  = e((string) plugin_setting('privacy_consent', 'message', ''));
        $accept   = e((string) plugin_setting('privacy_consent', 'accept_label', 'Accept'));
        $reject   = e((string) plugin_setting('privacy_consent', 'reject_label', 'Reject'));
        $polLabel = e((string) plugin_setting('privacy_consent', 'policy_label', 'Privacy Policy'));
        $polUrl   = e((string) plugin_setting('privacy_consent', 'policy_url', ''));
        $position = plugin_setting('privacy_consent', 'position', 'bottom') === 'top' ? 'top' : 'bottom';
        $endpoint = e(url('/api/v1/privacy/consent'));

        $policyHtml = $polUrl !== ''
            ? "<a href=\"{$polUrl}\" target=\"_blank\" style=\"color:#9ecbff;margin-left:6px;\">{$polLabel}</a>"
            : '';

        $posCss = $position === 'top' ? 'top:0;' : 'bottom:0;';

        return <<<HTML

<style>
#nice-cookie-banner{position:fixed;left:0;right:0;{$posCss}z-index:99999;background:#1f2937;color:#f3f4f6;
padding:14px 18px;display:none;box-shadow:0 -2px 12px rgba(0,0,0,.2);font-size:14px;line-height:1.5;}
#nice-cookie-banner .ncb-wrap{max-width:1100px;margin:0 auto;display:flex;align-items:center;gap:14px;flex-wrap:wrap;justify-content:space-between;}
#nice-cookie-banner .ncb-actions{display:flex;gap:8px;flex-shrink:0;}
#nice-cookie-banner button{border:0;border-radius:6px;padding:8px 16px;cursor:pointer;font-size:14px;}
#nice-cookie-banner .ncb-accept{background:#2563eb;color:#fff;}
#nice-cookie-banner .ncb-reject{background:#374151;color:#e5e7eb;}
</style>
<div id="nice-cookie-banner">
  <div class="ncb-wrap">
    <div class="ncb-msg">{$message}{$policyHtml}</div>
    <div class="ncb-actions">
      <button class="ncb-reject" type="button">{$reject}</button>
      <button class="ncb-accept" type="button">{$accept}</button>
    </div>
  </div>
</div>
<script>
(function(){
  var KEY='nice_cookie_consent';
  function setConsent(v){
    try{localStorage.setItem(KEY,v);}catch(e){}
    document.cookie=KEY+'='+v+';path=/;max-age=31536000';
    var b=document.getElementById('nice-cookie-banner'); if(b) b.style.display='none';
    try{
      fetch('{$endpoint}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},
        body:JSON.stringify({choice:v})});
    }catch(e){}
  }
  document.addEventListener('DOMContentLoaded',function(){
    var has=null; try{has=localStorage.getItem(KEY);}catch(e){}
    var b=document.getElementById('nice-cookie-banner'); if(!b) return;
    if(!has){ b.style.display='block'; }
    b.querySelector('.ncb-accept').addEventListener('click',function(){setConsent('accept');});
    b.querySelector('.ncb-reject').addEventListener('click',function(){setConsent('reject');});
  });
})();
</script>

HTML;
    }
}
