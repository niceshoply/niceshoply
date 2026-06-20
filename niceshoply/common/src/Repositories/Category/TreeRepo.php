<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Repositories\Category;

use Exception;
use Illuminate\Support\Facades\Cache;
use NiceShoply\Common\Models\Category;

class TreeRepo
{
    private static array $children = [];

    private static array $categories = [];

    public const CACHE_KEY = 'category_tree';

    public const CACHE_TTL = 3600; // 1 hour

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * @param  int  $parentId
     * @return array
     * @throws Exception
     */
    public function getCategoryTree(int $parentId = 0): array
    {
        $cacheKey = self::CACHE_KEY.'_'.$parentId;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($parentId) {
            return $this->buildCategoryTree($parentId);
        });
    }

    /**
     * @param  int  $parentId
     * @return array
     * @throws Exception
     */
    private function buildCategoryTree(int $parentId = 0): array
    {
        $categoryIDs = $this->getChildrenIds($parentId);
        $categories  = $this->getFlattenCategories($categoryIDs);
        foreach ($categories as $index => $category) {
            $categories[$index]['children'] = $this->buildCategoryTree($category['id']);
        }

        return $categories;
    }

    /**
     * Clear category tree cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY.'_0');
        self::$children   = [];
        self::$categories = [];
    }

    /**
     * @param  int  $parentId
     * @return array|mixed
     */
    private function getChildrenIds(int $parentId = 0): mixed
    {
        $allChildrenIDs = $this->getAllChildrenIds();

        return $allChildrenIDs[$parentId] ?? [];
    }

    /**
     * @return array
     */
    private function getAllChildrenIds(): array
    {
        if (self::$children) {
            return self::$children;
        }
        $categories = Category::query()
            ->select(['id', 'parent_id'])
            ->orderBy('categories.position')
            ->orderBy('categories.parent_id')
            ->where('active', true)
            ->get();

        $result = [];
        foreach ($categories as $category) {
            $result[$category['parent_id']][] = $category['id'];
        }
        self::$children = $result;

        return $result;
    }

    /**
     * @param  array  $categoryIDs
     * @return array
     * @throws Exception
     */
    private function getFlattenCategories(array $categoryIDs = []): array
    {
        $result = [];
        if (empty($categoryIDs)) {
            return $result;
        }

        $allCategories = $this->getAllFlattenCategories();
        foreach ($categoryIDs as $categoryId) {
            if (isset($allCategories[$categoryId])) {
                $result[] = $allCategories[$categoryId];
            }
        }

        return $result;
    }

    /**
     * @return array
     * @throws Exception
     */
    private static function getAllFlattenCategories(): array
    {
        if (self::$categories) {
            return self::$categories;
        }

        $categories = Category::query()
            ->with('translation')
            ->select(['id', 'slug', 'parent_id', 'image', 'active'])
            ->where('active', true)
            ->get();

        $result = [];
        foreach ($categories as $category) {
            $result[$category['id']] = [
                'id'     => $category->id,
                'slug'   => $category->slug,
                'name'   => $category->translation->name,
                'url'    => $category->url,
                'image'  => image_resize($category->image, 300, 300),
                'active' => (bool) $category->active,
            ];
        }
        self::$categories = $result;

        return $result;
    }
}
