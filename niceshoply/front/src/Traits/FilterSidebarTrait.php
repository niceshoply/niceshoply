<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Front\Traits;

use Illuminate\Http\Request;
use NiceShoply\Common\Repositories\AttributeRepo;
use NiceShoply\Common\Repositories\BrandRepo;
use NiceShoply\Common\Services\FilterSidebarService;

/**
 * Filter sidebar data processing Trait
 * Encapsulates repetitive filter data processing logic in controllers
 */
trait FilterSidebarTrait
{
    /**
     * Get filter sidebar data
     *
     * @param  Request  $request  HTTP request object
     * @return array Returns processed filter data array
     */
    protected function getFilterSidebarData(Request $request): array
    {
        // Get brand data
        $brands = BrandRepo::getInstance()->withActive()->all();

        // Get attribute data
        $attributes = AttributeRepo::getInstance()->getAttributesWithValues();

        // Use FilterSidebarService to process filter data
        $filterSidebarService = FilterSidebarService::getInstance();

        return [
            'brands'        => $filterSidebarService->processBrands($brands, $request),
            'attributes'    => $filterSidebarService->processAttributes($attributes, $request),
            'availability'  => $filterSidebarService->processAvailability($request),
            'price_filters' => $filterSidebarService->getPriceFilters($request),
        ];
    }
}
