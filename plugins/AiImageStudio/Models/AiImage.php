<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\AiImageStudio\Models;

use Illuminate\Database\Eloquent\Model;

class AiImage extends Model
{
    protected $table = 'ai_images';

    public $timestamps = false;

    protected $guarded = [];

    public function getUrlAttribute(): string
    {
        return asset('storage/'.ltrim($this->path, '/'));
    }
}
