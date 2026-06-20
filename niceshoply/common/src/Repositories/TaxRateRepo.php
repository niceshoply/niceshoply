<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Repositories;

use Illuminate\Database\Eloquent\Builder;
use NiceShoply\Common\Models\TaxRate;

class TaxRateRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'regions', 'type' => 'input', 'label' => trans('console/menu.regions')],
            ['name' => 'taxes', 'type' => 'input', 'label' => trans('console/tax_classes.taxes')],
            ['name' => 'type', 'type' => 'input', 'label' => trans('console/tax_classes.type')],
            ['name' => 'tax_rate', 'type' => 'input', 'label' => trans('console/tax_classes.tax_rate')],
            ['name' => 'created_at', 'type' => 'date_range', 'label' => trans('console/common.created_at')],
        ];
    }

    /**
     * @param  $taxRateId
     * @return string
     */
    public function getNameByRateId($taxRateId): string
    {
        $taxRate = TaxRate::query()->find($taxRateId);

        return $taxRate->name ?? '';
    }

    /**
     * @param  $filters
     * @return Builder
     */
    public function builder($filters = []): Builder
    {
        $builder = TaxRate::query();

        $createdStart = $filters['created_at_start'] ?? '';
        if ($createdStart) {
            $builder->where('created_at', '>', $createdStart);
        }

        $createdEnd = $filters['created_at_end'] ?? '';
        if ($createdEnd) {
            $builder->where('created_at', '<', $createdEnd);
        }

        return $builder;
    }
}
