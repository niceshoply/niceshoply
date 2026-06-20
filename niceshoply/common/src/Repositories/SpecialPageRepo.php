<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Repositories;

use Exception;

class SpecialPageRepo
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function getOptions(): array
    {
        $specialOptions = [
            ['type' => 'products', 'title' => trans('console/setting.products'), 'route' => 'products.index'],
            ['type' => 'brands', 'title' => trans('console/setting.brands'), 'route' => 'brands.index'],
        ];

        return fire_hook_filter('repo.special.options', $specialOptions);
    }

    /**
     * @param  $specials
     * @return array
     * @throws Exception
     */
    public function getSpecialLinks($specials): array
    {
        if (empty($specials)) {
            return [];
        }

        $items = [];
        foreach ($specials as $special) {
            if ($special == 'brands') {
                $url = front_route('brands.index');
            } elseif ($special == 'products') {
                $url = front_route('products.index');
            } else {
                continue;
            }
            $items[] = [
                'name' => trans('front/common.'.$special),
                'url'  => $url,
            ];
        }

        return fire_hook_filter('repo.special.links', $items);
    }
}
