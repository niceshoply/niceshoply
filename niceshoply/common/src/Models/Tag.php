<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use NiceShoply\Common\Traits\Translatable;

class Tag extends BaseModel
{
    use Translatable;

    protected $fillable = [
        'slug', 'position', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_tags', 'tag_id', 'article_id');
    }

    /**
     * Get slug url link.
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        if ($this->slug) {
            return front_route('tags.slug_show', ['slug' => $this->slug]);
        }

        return front_route('tags.show', $this);
    }
}
