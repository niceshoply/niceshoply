<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Warehouse;

class AllocationResult
{
    public bool $usedFallback = false;

    public array $matchedWarehouseIds = [];

    /**
     * @param  array  $allocations  [warehouse_id => [['sku_code' => '...', 'quantity' => N], ...]]
     * @param  bool  $isSplit  Whether the order needs to be split across warehouses
     * @param  array  $warehouseGroups  [warehouse_id => Warehouse model, ...]
     * @param  array  $insufficientSkus  SKU codes that don't have enough stock
     */
    public function __construct(
        public array $allocations = [],
        public bool $isSplit = false,
        public array $warehouseGroups = [],
        public array $insufficientSkus = [],
    ) {}

    /**
     * @return bool
     */
    public function isFullyAllocated(): bool
    {
        return empty($this->insufficientSkus);
    }

    /**
     * Get the number of packages (warehouses) needed.
     *
     * @return int
     */
    public function getPackageCount(): int
    {
        return count($this->allocations);
    }

    /**
     * Generate customer-facing allocation messages for checkout display.
     *
     * @return array
     */
    public function getCheckoutMessages(): array
    {
        $messages = [];

        foreach ($this->allocations as $warehouseId => $items) {
            $warehouse     = $this->warehouseGroups[$warehouseId] ?? null;
            $warehouseName = $warehouse->name ?? '';
            $isLocal       = in_array($warehouseId, $this->matchedWarehouseIds);

            foreach ($items as $item) {
                if ($isLocal) {
                    $messages[] = [
                        'sku_code' => $item['sku_code'],
                        'type'     => 'local',
                        'message'  => trans('front/checkout.warehouse_ship_from', ['warehouse' => $warehouseName]),
                    ];
                } else {
                    $messages[] = [
                        'sku_code' => $item['sku_code'],
                        'type'     => 'fallback',
                        'message'  => trans('front/checkout.warehouse_fallback_ship', ['warehouse' => $warehouseName]),
                    ];
                }
            }
        }

        foreach ($this->insufficientSkus as $skuCode) {
            $messages[] = [
                'sku_code' => $skuCode,
                'type'     => 'insufficient',
                'message'  => trans('front/checkout.warehouse_out_of_stock'),
            ];
        }

        return $messages;
    }

    /**
     * Convert to array for API response.
     *
     * @return array
     */
    public function toArray(): array
    {
        $packages = [];
        foreach ($this->allocations as $warehouseId => $items) {
            $warehouse  = $this->warehouseGroups[$warehouseId] ?? null;
            $packages[] = [
                'warehouse_id'   => $warehouseId,
                'warehouse_name' => $warehouse->name ?? '',
                'warehouse_code' => $warehouse->code ?? '',
                'is_local'       => in_array($warehouseId, $this->matchedWarehouseIds),
                'items'          => $items,
            ];
        }

        return [
            'packages'          => $packages,
            'is_split'          => $this->isSplit,
            'package_count'     => $this->getPackageCount(),
            'insufficient_skus' => $this->insufficientSkus,
            'fully_allocated'   => $this->isFullyAllocated(),
            'used_fallback'     => $this->usedFallback,
            'messages'          => $this->getCheckoutMessages(),
        ];
    }
}
