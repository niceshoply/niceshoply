<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Front\Repositories;

use Exception;
use NiceShoply\Common\Repositories\CatalogRepo;
use NiceShoply\Common\Repositories\CategoryRepo;
use NiceShoply\Common\Repositories\PageRepo;
use NiceShoply\Common\Repositories\SpecialPageRepo;
use NiceShoply\Common\Resources\CatalogSimple;
use NiceShoply\Common\Resources\PageSimple;

class FooterMenuRepo
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * Generate header menus for frontend.
     *
     * @return array
     * @throws Exception
     */
    public function getMenus(): array
    {
        $menus = [
            'categories' => $this->getCategories(system_setting('menu_footer_categories')),
            'catalogs'   => $this->getCatalogs(system_setting('menu_footer_catalogs')),
            'pages'      => $this->getPages(system_setting('menu_footer_pages')),
            'specials'   => $this->getSpecials(system_setting('menu_footer_specials')),
        ];

        return fire_hook_filter('global.footer.menus', $menus);
    }

    /**
     * @param  $specials
     * @return array
     * @throws Exception
     */
    public function getSpecials($specials): array
    {
        if (empty($specials)) {
            return [];
        }

        return SpecialPageRepo::getInstance()->getSpecialLinks($specials);
    }

    /**
     * @param  $categoryIds
     * @return array
     */
    private function getCategories($categoryIds): array
    {
        if (empty($categoryIds)) {
            return [];
        }

        return CategoryRepo::getInstance()->getTwoLevelCategories($categoryIds);
    }

    /**
     * @param  $catalogIds
     * @return array
     */
    private function getCatalogs($catalogIds): array
    {
        if (empty($catalogIds)) {
            return [];
        }

        $catalogs = CatalogRepo::getInstance()
            ->builder(['active' => true, 'parent_id' => 0, 'catalog_ids' => $catalogIds])
            ->orderBy('position')
            ->get();

        return CatalogSimple::collection($catalogs)->jsonSerialize();
    }

    /**
     * @param  $pageIds
     * @return array
     */
    private function getPages($pageIds): array
    {
        if (empty($pageIds)) {
            return [];
        }

        $catalogs = PageRepo::getInstance()
            ->builder(['active' => true, 'page_ids' => $pageIds])
            ->orderBy('position')
            ->get();

        return PageSimple::collection($catalogs)->jsonSerialize();
    }
}
