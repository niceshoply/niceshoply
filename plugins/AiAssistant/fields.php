<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    ['name' => 'enabled', 'label_key' => 'common.enabled', 'type' => 'switch', 'default' => true],
    ['name' => 'assistant_name', 'label_key' => 'common.assistant_name', 'type' => 'string', 'default' => '小助手'],
    ['name' => 'welcome', 'label_key' => 'common.welcome', 'type' => 'textarea', 'default' => '你好，我是您的购物助手，有什么可以帮您？'],
    ['name' => 'system_prompt', 'label_key' => 'common.system_prompt', 'type' => 'textarea', 'default' => '你是本商城的专业导购客服，基于提供的知识库与商品信息，用简洁友好的中文回答顾客问题。不要编造不存在的商品或政策；无法确定时引导顾客联系人工客服。'],
    // 拼接进 prompt 的知识库条目上限
    ['name' => 'kb_limit', 'label_key' => 'common.kb_limit', 'type' => 'string', 'default' => '8', 'rules' => 'nullable|integer|min:1|max:30'],
    // 是否注入前台对话挂件
    ['name' => 'inject_widget', 'label_key' => 'common.inject_widget', 'type' => 'switch', 'default' => true],
];
