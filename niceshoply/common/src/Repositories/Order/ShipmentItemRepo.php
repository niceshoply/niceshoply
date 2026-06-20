<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Repositories\Order;

use Illuminate\Database\Eloquent\Builder;
use NiceShoply\Common\Models\Order\ShipmentItem;
use NiceShoply\Common\Repositories\BaseRepo;

class ShipmentItemRepo extends BaseRepo
{
    protected string $model = ShipmentItem::class;

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = ShipmentItem::query();
        $filters = array_merge($this->filters, $filters);

        $shipmentId = $filters['shipment_id'] ?? 0;
        if ($shipmentId) {
            $builder->where('shipment_id', $shipmentId);
        }

        $orderItemId = $filters['order_item_id'] ?? 0;
        if ($orderItemId) {
            $builder->where('order_item_id', $orderItemId);
        }

        return $builder;
    }
}
