<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

use NiceShoply\Common\Traits\Translatable;

/**
 * 顶部公告模型（多语言）
 */
class Announcement extends BaseModel
{
    use Translatable;

    protected $table = 'announcements';

    protected $fillable = [
        'plugin_code',
        'url',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'active'     => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * 获取激活的公告（按 sort_order 排序，解析为当前语言文本）。
     *
     * @return array
     */
    public static function getActiveItems(): array
    {
        return static::with(['translation', 'translations'])
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => [
                'text' => $row->translation->text ?? $row->translations->first()?->text ?? '',
                'url'  => $row->url ?: '',
            ])
            ->filter(fn ($item) => $item['text'] !== '')
            ->values()
            ->all();
    }
}
