<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Repositories;

use Illuminate\Support\Facades\Cache;

/**
 * 后台菜单全局搜索仓库（IMP-08）
 *
 * 将侧边栏菜单扁平化为可搜索条目（标题 + 分组关键词 + URL），
 * 并按当前管理员权限过滤，供后台顶部全局搜索框使用。
 */
class MenuSearchRepo
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 按关键词搜索菜单。
     *
     * @param  string  $keyword
     * @return array
     */
    public function search(string $keyword = ''): array
    {
        $admin = current_admin();
        if (! $admin) {
            return [];
        }

        $cacheKey = 'menu_search:'.$admin->getAuthIdentifier().':'.app()->getLocale();
        $items    = Cache::remember($cacheKey, 60, fn () => $this->getSearchableMenus($admin));

        $keyword = trim($keyword);
        if ($keyword === '') {
            return $items;
        }

        $keyword = mb_strtolower($keyword);

        return array_values(array_filter($items, function ($item) use ($keyword) {
            $haystack = mb_strtolower($item['title'].' '.($item['keywords'] ?? ''));

            return str_contains($haystack, $keyword);
        }));
    }

    /**
     * 获取全部可搜索菜单（已按权限过滤）。
     *
     * @param  mixed  $admin
     * @return array
     */
    public function getSearchableMenus($admin): array
    {
        $items = [];

        foreach ($this->getMenuGroups() as $group) {
            $groupTitle = $group['title'] ?? '';

            foreach ($group['children'] ?? [] as $child) {
                $route = $child['route'] ?? '';
                if ($route === '') {
                    continue;
                }

                // 权限校验：与侧边栏一致使用 route 转下划线作为权限码
                $skipPermission = $child['skip_permission'] ?? false;
                if (! $skipPermission) {
                    $code = str_replace('.', '_', $route);
                    if (! $admin->can($code)) {
                        continue;
                    }
                }

                $items[] = [
                    'title'    => $child['title'] ?? '',
                    'url'      => $this->resolveUrl($route, $child['url'] ?? ''),
                    'route'    => $route,
                    'keywords' => $groupTitle,
                ];
            }
        }

        return $items;
    }

    /**
     * 菜单分组定义（与 Sidebar 保持一致）。
     *
     * @return array
     */
    private function getMenuGroups(): array
    {
        $groups = [
            [
                'title'    => __('console/menu.dashboard'),
                'children' => [
                    ['route' => 'dashboard.index', 'title' => __('console/menu.dashboard'), 'skip_permission' => true],
                ],
            ],
            [
                'title'    => __('console/menu.top_order'),
                'children' => [
                    ['route' => 'orders.index', 'title' => __('console/menu.orders')],
                    ['route' => 'order_returns.index', 'title' => __('console/menu.order_returns')],
                    ['route' => 'return_reasons.index', 'title' => __('console/menu.return_reasons')],
                    ['route' => 'warehouses.index', 'title' => __('console/menu.warehouses')],
                    ['route' => 'warehouse_stocks.index', 'title' => __('console/menu.warehouse_stocks')],
                    ['route' => 'stock_transfers.index', 'title' => __('console/menu.stock_transfers')],
                ],
            ],
            [
                'title'    => __('console/menu.top_product'),
                'children' => [
                    ['route' => 'products.index', 'title' => __('console/menu.products')],
                    ['route' => 'categories.index', 'title' => __('console/menu.categories')],
                    ['route' => 'brands.index', 'title' => __('console/menu.brands')],
                    ['route' => 'attributes.index', 'title' => __('console/menu.attributes')],
                    ['route' => 'options.index', 'title' => __('console/menu.options')],
                    ['route' => 'reviews.index', 'title' => __('console/menu.reviews')],
                ],
            ],
            [
                'title'    => __('console/menu.top_customer'),
                'children' => [
                    ['route' => 'customers.index', 'title' => __('console/menu.customers')],
                    ['route' => 'customer_groups.index', 'title' => __('console/menu.customer_groups')],
                    ['route' => 'member_levels.index', 'title' => __('console/menu.member_levels')],
                    ['route' => 'point_logs.index', 'title' => __('console/menu.point_logs')],
                    ['route' => 'transactions.index', 'title' => __('console/menu.transactions')],
                    ['route' => 'withdrawals.index', 'title' => __('console/menu.withdrawals')],
                    ['route' => 'socials.index', 'title' => __('console/menu.sns')],
                ],
            ],
            [
                'title'    => __('console/menu.top_content'),
                'children' => [
                    ['route' => 'articles.index', 'title' => __('console/menu.articles')],
                    ['route' => 'catalogs.index', 'title' => __('console/menu.catalogs')],
                    ['route' => 'tags.index', 'title' => __('console/menu.tags')],
                    ['route' => 'pages.index', 'title' => __('console/menu.pages')],
                    ['route' => 'file_manager.index', 'title' => __('console/menu.file_manager')],
                ],
            ],
            [
                'title'    => __('console/menu.top_design'),
                'children' => [
                    ['route' => 'themes_settings.index', 'title' => __('console/menu.themes_settings')],
                    ['route' => 'themes.index', 'title' => __('console/menu.themes')],
                ],
            ],
            [
                'title'    => __('console/menu.top_analytic'),
                'children' => [
                    ['route' => 'analytics.index', 'title' => __('console/menu.analytics')],
                ],
            ],
            [
                'title'    => __('console/menu.top_marketing'),
                'children' => [
                    ['route' => 'promotions.index', 'title' => __('console/menu.promotions')],
                    ['route' => 'coupons.index', 'title' => __('console/menu.coupons')],
                    ['route' => 'abandoned_carts.index', 'title' => __('console/menu.abandoned_carts')],
                ],
            ],
            [
                'title'    => __('console/menu.top_setting'),
                'children' => [
                    ['route' => 'settings.index', 'title' => __('console/menu.settings')],
                    ['route' => 'account.index', 'title' => __('console/menu.account')],
                    ['route' => 'admins.index', 'title' => __('console/menu.admins')],
                    ['route' => 'roles.index', 'title' => __('console/menu.roles')],
                    ['route' => 'audit_logs.index', 'title' => __('console/menu.audit_logs')],
                    ['route' => 'countries.index', 'title' => __('console/menu.countries')],
                    ['route' => 'states.index', 'title' => __('console/menu.states')],
                    ['route' => 'regions.index', 'title' => __('console/menu.regions')],
                    ['route' => 'locales.index', 'title' => __('console/menu.locales')],
                    ['route' => 'currencies.index', 'title' => __('console/menu.currencies')],
                    ['route' => 'tax_rates.index', 'title' => __('console/menu.tax_rates')],
                    ['route' => 'tax_classes.index', 'title' => __('console/menu.tax_classes')],
                    ['route' => 'weight_classes.index', 'title' => __('console/menu.weight_classes')],
                    ['route' => 'redirects.index', 'title' => __('console/menu.redirects')],
                    ['route' => 'legal_documents.index', 'title' => __('console/menu.legal_documents')],
                    ['route' => 'gdpr_requests.index', 'title' => __('console/menu.gdpr_requests')],
                    ['route' => 'backups.index', 'title' => __('console/menu.backups')],
                    ['route' => 'health.index', 'title' => __('console/menu.health')],
                    ['route' => 'schedule.index', 'title' => __('console/menu.schedule')],
                ],
            ],
        ];

        return fire_hook_filter('console.menu_search.groups', $groups);
    }

    /**
     * 解析路由 URL。
     *
     * @param  string  $route
     * @param  string  $fallbackUrl
     * @return string
     */
    private function resolveUrl(string $route, string $fallbackUrl = ''): string
    {
        try {
            return console_route($route);
        } catch (\Exception $e) {
            return $fallbackUrl;
        }
    }
}
