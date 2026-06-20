<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NiceShoply\Common\Traits\Translatable;

class Catalog extends BaseModel
{
    use Translatable;

    protected $fillable = [
        'parent_id', 'slug', 'image', 'position', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Model validation rules to prevent circular references
     */
    protected static function booted(): void
    {
        static::saving(function ($catalog) {
            // Check parent cannot be itself
            if ($catalog->parent_id && $catalog->parent_id == $catalog->id) {
                throw new \Exception(trans('console/common.category_parent_self'));
            }

            // Check for circular references
            if ($catalog->parent_id && $catalog->parent_id > 0) {
                $visited       = [$catalog->id];
                $currentParent = self::find($catalog->parent_id);

                while ($currentParent) {
                    if (in_array($currentParent->id, $visited)) {
                        throw new \Exception(trans('console/common.category_circular_reference'));
                    }
                    $visited[]     = $currentParent->id;
                    $currentParent = $currentParent->parent;
                }
            }
        });
    }

    public $appends = [
        'url',
    ];

    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Catalog::class, 'parent_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Catalog::class, 'parent_id', 'id');
    }

    /**
     * Get slug url link.
     *
     * @return string
     * @throws \Exception
     */
    public function getUrlAttribute(): string
    {
        if ($this->slug) {
            return front_route('catalogs.slug_show', ['slug' => $this->slug]);
        }

        return front_route('catalogs.show', $this);
    }

    /**
     * 目录封面图缩略 URL（默认 600x600）。
     *
     * @param  int  $width
     * @param  int  $height
     * @return string
     * @throws \Exception
     */
    public function getImageUrl(int $width = 600, int $height = 600): string
    {
        return image_resize($this->image ?? '', $width, $height);
    }
}
