<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Product;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NiceShoply\Common\Models\BaseModel;
use NiceShoply\Common\Models\Product;

class Relation extends BaseModel
{
    protected $table = 'product_relations';

    protected $fillable = [
        'product_id', 'relation_id',
    ];

    /**
     * Get the main product
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the related product
     * @return BelongsTo
     */
    public function relationProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'relation_id');
    }
}
