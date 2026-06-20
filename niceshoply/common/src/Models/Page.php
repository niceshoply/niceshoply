<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

use Exception;
use NiceShoply\Common\Traits\Translatable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Page extends BaseModel
{
    use LogsActivity, Translatable;

    protected $fillable = [
        'slug', 'viewed', 'show_breadcrumb', 'active',
    ];

    protected $casts = [
        'show_breadcrumb' => 'boolean',
        'active'          => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Page {$eventName}")
            ->useLogName('admin');
    }

    public $appends = [
        'url',
    ];

    /**
     * Get slug url link.
     * Uses page-{slug} pattern to maintain consistency with other resources (product-{slug}, category-{slug}, article-{slug})
     *
     * @return string
     * @throws Exception
     */
    public function getUrlAttribute(): string
    {
        try {
            if ($this->slug) {
                return front_route('pages.slug_show', ['slug' => $this->slug]);
            }

            return front_route('pages.show', $this);
        } catch (Exception $e) {
            return '';
        }
    }
}
