<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'         => '搜索增强',
    'driver'       => '搜索驱动',
    'driver_db'    => '数据库（开箱即用）',
    'driver_meili' => 'Meilisearch',
    'limit'        => '结果数量上限',
    'fallback_hot' => '无结果时推荐热销',
    'meili_host'   => 'Meilisearch 地址',
    'meili_key'    => 'Meilisearch API Key',
    'meili_index'  => 'Meilisearch 索引名',

    'saved'        => '已保存',
    'deleted'      => '已删除',
    'reindexed'    => '已重建索引，共 :count 条',
    'no_meili'     => '请先配置 Meilisearch 地址',

    // console
    'title'        => '搜索增强',
    'current_driver' => '当前驱动',
    'reindex'      => '重建 Meilisearch 索引',
    'hotwords'     => '热搜词 TOP 30',
    'noresults'    => '无结果搜索词（需补品/同义词）',
    'keyword'      => '搜索词',
    'hits'         => '次数',
    'last_at'      => '最近搜索',
    'synonyms'     => '同义词组',
    'syn_tip'      => '逗号分隔，如：手机,智能手机,cellphone。命中任一词时扩展为整组检索。',
    'terms'        => '词组',
    'add'          => '添加',
    'del'          => '删除',
    'no_data'      => '暂无数据',
    'confirm_del'  => '确认删除？',
    'api_title'    => '前台调用接口（前缀 /api/v1）',
];
