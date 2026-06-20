<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\AiAssistant\Services;

use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Services\AI\AIServiceManager;
use Plugin\AiAssistant\Models\Conversation;
use Plugin\AiAssistant\Models\KbEntry;
use RuntimeException;

class AiAssistantService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (bool) plugin_setting('ai_assistant', 'enabled', true);
    }

    /**
     * 回答顾客问题。
     *
     * @throws RuntimeException
     */
    public function answer(string $question, string $visitorKey = ''): string
    {
        $question = trim($question);
        if ($question === '') {
            throw new RuntimeException(__('AiAssistant::common.empty_question'));
        }

        if (! class_exists(AIServiceManager::class)) {
            throw new RuntimeException(__('AiAssistant::common.ai_unavailable'));
        }

        $prompt = $this->buildPrompt($question);

        $answer = trim((string) AIServiceManager::getInstance()->generate($prompt, 'chat', [
            'temperature' => 0.5,
            'max_tokens'  => 800,
        ]));

        Conversation::query()->create([
            'visitor_key' => $visitorKey !== '' ? mb_substr($visitorKey, 0, 64) : 'anonymous',
            'question'    => $question,
            'answer'      => $answer,
            'created_at'  => now(),
        ]);

        return $answer;
    }

    protected function buildPrompt(string $question): string
    {
        $system = (string) plugin_setting('ai_assistant', 'system_prompt', '');
        $kb     = $this->matchedKnowledge($question);
        $hot    = $this->hotProductsContext();

        $parts = [];
        $parts[] = '【角色与规则】'.PHP_EOL.$system;
        if ($kb !== '') {
            $parts[] = '【知识库】'.PHP_EOL.$kb;
        }
        if ($hot !== '') {
            $parts[] = '【热销商品（可推荐）】'.PHP_EOL.$hot;
        }
        $parts[] = '【顾客问题】'.PHP_EOL.$question;
        $parts[] = '请基于以上信息作答；信息不足时礼貌说明并建议联系人工客服。';

        return implode(PHP_EOL.PHP_EOL, $parts);
    }

    /**
     * 召回与问题相关的知识库条目（关键词命中优先，不足则取靠前条目）。
     */
    protected function matchedKnowledge(string $question): string
    {
        $limit = max(1, min((int) plugin_setting('ai_assistant', 'kb_limit', 8), 30));

        $all = KbEntry::query()->where('is_active', true)->orderBy('sort')->orderBy('id')->get();
        if ($all->isEmpty()) {
            return '';
        }

        // 简单中文/英文分词：按空白和常见标点切
        $tokens = array_filter(preg_split('/[\s,，。、?？!！]+/u', $question) ?: []);

        $scored = $all->map(function ($e) use ($tokens) {
            $hay = $e->title.' '.$e->content;
            $score = 0;
            foreach ($tokens as $t) {
                if ($t !== '' && mb_stripos($hay, $t) !== false) {
                    $score++;
                }
            }

            return ['entry' => $e, 'score' => $score];
        })->sortByDesc('score')->values();

        $picked = $scored->take($limit)->map(fn ($s) => '- '.$s['entry']->title.'：'.$s['entry']->content);

        return $picked->implode(PHP_EOL);
    }

    protected function hotProductsContext(): string
    {
        $products = Product::query()
            ->with('translation')
            ->where('active', 1)
            ->orderByDesc('sales')
            ->limit(8)
            ->get();

        if ($products->isEmpty()) {
            return '';
        }

        return $products->map(fn ($p) => '- '.(optional($p->translation)->name ?? ('#'.$p->id)).'（'.currency_format((float) $p->price).'）')
            ->implode(PHP_EOL);
    }

    /**
     * 前台对话挂件 HTML。
     */
    public function renderWidget(): string
    {
        if (! $this->enabled() || ! (bool) plugin_setting('ai_assistant', 'inject_widget', true)) {
            return '';
        }

        $name     = e((string) plugin_setting('ai_assistant', 'assistant_name', '小助手'));
        $welcome  = e((string) plugin_setting('ai_assistant', 'welcome', ''));
        $endpoint = e(url('/api/v1/assistant/chat'));

        return <<<HTML

<style>
#nice-ai-bubble{position:fixed;right:20px;bottom:20px;z-index:99998;width:56px;height:56px;border-radius:50%;
background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 4px 16px rgba(0,0,0,.25);font-size:24px;}
#nice-ai-panel{position:fixed;right:20px;bottom:88px;z-index:99998;width:340px;max-width:92vw;height:460px;max-height:70vh;
background:#fff;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.25);display:none;flex-direction:column;overflow:hidden;}
#nice-ai-panel .ai-head{background:#2563eb;color:#fff;padding:12px 14px;font-weight:600;}
#nice-ai-msgs{flex:1;overflow-y:auto;padding:12px;background:#f8fafc;font-size:14px;}
#nice-ai-msgs .m{margin-bottom:10px;display:flex;}
#nice-ai-msgs .m.u{justify-content:flex-end;}
#nice-ai-msgs .b{max-width:80%;padding:8px 10px;border-radius:10px;white-space:pre-wrap;line-height:1.5;}
#nice-ai-msgs .a .b{background:#fff;border:1px solid #e5e7eb;}
#nice-ai-msgs .u .b{background:#2563eb;color:#fff;}
#nice-ai-foot{display:flex;border-top:1px solid #eee;}
#nice-ai-foot input{flex:1;border:0;padding:12px;outline:none;font-size:14px;}
#nice-ai-foot button{border:0;background:#2563eb;color:#fff;padding:0 16px;cursor:pointer;}
</style>
<div id="nice-ai-bubble">💬</div>
<div id="nice-ai-panel">
  <div class="ai-head">{$name}</div>
  <div id="nice-ai-msgs"></div>
  <div id="nice-ai-foot">
    <input id="nice-ai-input" type="text" placeholder="输入您的问题..." />
    <button id="nice-ai-send">发送</button>
  </div>
</div>
<script>
(function(){
  var bubble=document.getElementById('nice-ai-bubble'),panel=document.getElementById('nice-ai-panel'),
      msgs=document.getElementById('nice-ai-msgs'),input=document.getElementById('nice-ai-input'),
      send=document.getElementById('nice-ai-send'),inited=false;
  function add(role,text){var m=document.createElement('div');m.className='m '+(role==='u'?'u':'a');
    var b=document.createElement('div');b.className='b';b.textContent=text;m.appendChild(b);msgs.appendChild(m);msgs.scrollTop=msgs.scrollHeight;return b;}
  bubble.addEventListener('click',function(){
    panel.style.display=panel.style.display==='flex'?'none':'flex';
    if(!inited && "{$welcome}"){add('a',"{$welcome}");inited=true;}
  });
  function ask(){
    var q=input.value.trim(); if(!q) return; input.value='';
    add('u',q); var bot=add('a','...');
    fetch('{$endpoint}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},
      body:JSON.stringify({message:q})}).then(function(r){return r.json();}).then(function(res){
        bot.textContent=(res && res.data && res.data.reply) ? res.data.reply : (res.message||'抱歉，暂时无法回答');
      }).catch(function(){bot.textContent='网络异常，请稍后再试';});
  }
  send.addEventListener('click',ask);
  input.addEventListener('keydown',function(e){if(e.key==='Enter')ask();});
})();
</script>

HTML;
    }
}
