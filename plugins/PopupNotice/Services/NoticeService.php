<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PopupNotice\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Plugin\PopupNotice\Models\SiteNotice;

class NoticeService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 当前生效的公告/弹窗。
     */
    public function activeNotices(): Collection
    {
        $now = Carbon::now();

        return SiteNotice::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', $now))
            ->orderBy('sort')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * 生成注入前台 head 的 HTML（顶部公告条 + 弹窗 + 脚本）。
     */
    public function render(): string
    {
        if (! (bool) plugin_setting('popup_notice', 'enabled', true)) {
            return '';
        }

        $notices = $this->activeNotices();
        if ($notices->isEmpty()) {
            return '';
        }

        $bars   = $notices->where('type', 'bar');
        $popups = $notices->where('type', 'popup');

        $html = $this->styles();

        foreach ($bars as $bar) {
            $html .= $this->renderBar($bar);
        }
        foreach ($popups as $popup) {
            $html .= $this->renderPopup($popup);
        }

        $html .= $this->script();

        return $html;
    }

    protected function styles(): string
    {
        return '<style>'
            .'.ns-notice-bar{position:relative;width:100%;padding:8px 32px;text-align:center;background:#111;color:#fff;font-size:14px;z-index:1080}'
            .'.ns-notice-bar a{color:#fff;text-decoration:underline}'
            .'.ns-notice-bar .ns-close{position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;opacity:.8}'
            .'.ns-popup-mask{position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:1090}'
            .'.ns-popup{position:relative;max-width:90%;max-height:85%;background:#fff;border-radius:12px;overflow:hidden;text-align:center}'
            .'.ns-popup img{max-width:100%;display:block}'
            .'.ns-popup .ns-body{padding:20px}'
            .'.ns-popup .ns-close{position:absolute;right:10px;top:8px;cursor:pointer;font-size:22px;color:#888;line-height:1}'
            .'</style>';
    }

    protected function renderBar(SiteNotice $bar): string
    {
        $content = e($bar->content ?: $bar->title);
        if ($bar->link_url) {
            $content = '<a href="'.e($bar->link_url).'">'.$content.'</a>';
        }

        return '<div class="ns-notice-bar" data-id="'.$bar->id.'" data-scope="'.e($bar->scope).'">'
            .$content
            .'<span class="ns-close" data-close="bar" data-id="'.$bar->id.'">&times;</span>'
            .'</div>';
    }

    protected function renderPopup(SiteNotice $popup): string
    {
        $inner = '';
        if ($popup->image) {
            $img = '<img src="'.e($popup->image).'" alt="'.e($popup->title).'">';
            $inner = $popup->link_url ? '<a href="'.e($popup->link_url).'">'.$img.'</a>' : $img;
        } else {
            $body = '<div class="ns-body"><h5>'.e($popup->title).'</h5><div>'.nl2br(e($popup->content)).'</div>';
            if ($popup->link_url) {
                $body .= '<a class="btn btn-primary mt-2" href="'.e($popup->link_url).'">&rarr;</a>';
            }
            $body .= '</div>';
            $inner = $body;
        }

        return '<div class="ns-popup-mask" data-popup="'.$popup->id.'" data-scope="'.e($popup->scope).'" style="display:none">'
            .'<div class="ns-popup"><span class="ns-close" data-close="popup" data-id="'.$popup->id.'">&times;</span>'.$inner.'</div>'
            .'</div>';
    }

    protected function script(): string
    {
        return <<<'JS'
<script>
(function(){
  var isHome = location.pathname === '/' || /\/(index)?$/.test(location.pathname);
  function scopeOk(scope){ return scope === 'all' || (scope === 'home' && isHome); }
  function todayKey(prefix,id){ return 'ns_notice_'+prefix+'_'+id+'_'+new Date().toISOString().slice(0,10); }

  document.querySelectorAll('.ns-notice-bar').forEach(function(bar){
    if(!scopeOk(bar.getAttribute('data-scope')) || localStorage.getItem(todayKey('bar',bar.getAttribute('data-id')))){
      bar.style.display='none';
    }
  });
  document.querySelectorAll('.ns-popup-mask').forEach(function(mask){
    var id = mask.getAttribute('data-popup');
    if(scopeOk(mask.getAttribute('data-scope')) && !localStorage.getItem(todayKey('popup',id))){
      mask.style.display='flex';
    }
  });
  document.querySelectorAll('[data-close]').forEach(function(btn){
    btn.addEventListener('click', function(e){
      e.preventDefault();
      var kind = btn.getAttribute('data-close'); var id = btn.getAttribute('data-id');
      localStorage.setItem(todayKey(kind,id),'1');
      if(kind==='bar'){ btn.closest('.ns-notice-bar').style.display='none'; }
      else { btn.closest('.ns-popup-mask').style.display='none'; }
    });
  });
  document.querySelectorAll('.ns-popup-mask').forEach(function(mask){
    mask.addEventListener('click', function(e){ if(e.target===mask){ mask.style.display='none'; } });
  });
})();
</script>
JS;
    }
}
