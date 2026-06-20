<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Components\Layout;

use Illuminate\View\Component;
use NiceShoply\Plugin\Repositories\PluginTypeRepo;

class Sidebar extends Component
{
    public mixed $adminUser;

    public array $menuLinks = [];

    private string $currentUri;

    private string $currentRoute;

    private string $currentPrefix;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->adminUser = current_admin();

        $routeNameWithPrefix = request()->route()->getName();
        $this->currentRoute  = (string) str_replace(console_name().'.', '', $routeNameWithPrefix);

        $patterns = explode('.', $this->currentRoute);

        $this->currentPrefix = $patterns[0];

        $routeUriWithPrefix = request()->route()->uri();
        $this->currentUri   = (string) str_replace(console_name().'/', '', $routeUriWithPrefix);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return mixed
     */
    public function render(): mixed
    {
        $this->menuLinks = $this->handleMenus($this->getMenus());

        return view('console::components.layout.sidebar');
    }

    /**
     * Get all menus
     */
    private function getMenus(): array
    {
        $menus = [
            [
                'route' => 'dashboard.index',
                'title' => __('console/menu.dashboard'),
                'icon'  => 'bi-speedometer2',
            ],
            [
                'title'    => __('console/menu.top_order'),
                'icon'     => 'bi-cart',
                'prefixes' => ['orders', 'rmas', 'refunds', 'warehouses', 'warehouse_stocks', 'warehouse_stock_movements', 'stock_transfers'],
                'children' => $this->getOrderSubRoutes(),
            ],
            [
                'title'    => __('console/menu.top_product'),
                'icon'     => 'bi-bag',
                'prefixes' => ['products', 'options', 'option_values', 'attribute_groups'],
                'children' => $this->getProductSubRoutes(),
            ],
            [
                'title'    => __('console/menu.top_customer'),
                'icon'     => 'bi-person',
                'prefixes' => ['customers', 'withdrawals', 'member_levels', 'point_logs'],
                'children' => $this->getCustomerSubRoutes(),
            ],

            // Operation Management Divider
            [
                'type'  => 'divider',
                'title' => __('console/menu.divider_operation'),
            ],

            [
                'title'    => __('console/menu.top_marketing'),
                'icon'     => 'bi-broadcast',
                'prefixes' => ['promotions', 'coupons', 'abandoned_carts'],
                'children' => $this->getMarketingSubRoutes(),
            ],
            [
                'title'    => __('console/menu.top_content'),
                'icon'     => 'bi-sticky',
                'prefixes' => ['articles', 'catalogs', 'tags', 'pages'],
                'children' => $this->getContentSubRoutes(),
            ],
            [
                'title'    => __('console/menu.top_design'),
                'icon'     => 'bi-palette',
                'children' => $this->getDesignSubRoutes(),
            ],
            [
                'title'    => __('console/menu.top_analytic'),
                'icon'     => 'bi-bar-chart',
                'prefixes' => ['analytics', 'analytics_order', 'visits'],
                'children' => $this->getAnalyticSubRoutes(),
            ],

            // System Management Divider
            [
                'type'  => 'divider',
                'title' => __('console/menu.divider_system'),
            ],

            [
                'title'    => __('console/menu.top_plugin'),
                'icon'     => 'bi-puzzle',
                'prefixes' => ['plugins', 'plugin-market', 'theme-market'],
                'children' => $this->getPluginSubRoutes(),
            ],
            [
                'title'    => __('console/menu.top_setting'),
                'icon'     => 'bi-gear',
                'children' => $this->getSettingSubRoutes(),
            ],
        ];

        return fire_hook_filter('console.component.sidebar.menus', $menus);
    }

    /**
     * Handle menus like whether check or not.
     */
    private function handleMenus($links): array
    {
        $result      = [];
        $lastDivider = null;  // Store the last divider temporarily

        foreach ($links as $index => $link) {
            // If it's a divider, store it temporarily
            if (isset($link['type']) && $link['type'] == 'divider') {
                $lastDivider = $link;

                continue;
            }

            $topUrl   = $link['url'] ?? '';
            $topRoute = $link['route'] ?? '';
            if (empty($topUrl) && $topRoute) {
                $link['url'] = console_route($topRoute);
            }

            $parentChecked = false;
            if (isset($link['active'])) {
                $parentChecked = $link['active'];
            } elseif ($this->checkChildActive($topRoute)) {
                $parentChecked = true;
            }

            $prefixes = $link['prefixes'] ?? [];
            $children = $link['children'] ?? [];

            $link['has_children'] = (bool) $children;
            $hasVisibleChild      = false;  // Track if there are visible child menus

            foreach ($children as $key => $item) {
                $skipPermission = $item['skip_permission'] ?? false;
                if (! $skipPermission) {
                    $code = str_replace('.', '_', $item['route']);
                    if (! $this->adminUser->can($code)) {
                        unset($link['children'][$key]);

                        continue;
                    }
                }

                $hasVisibleChild = true;

                $url = $item['url'] ?? '';
                if (empty($url)) {
                    $item['url'] = console_route($item['route']);
                }

                if (isset($item['active'])) {
                    if ($item['active']) {
                        $parentChecked = true;
                    }
                } elseif ($this->checkChildActive($item['route'])) {
                    $item['active'] = true;
                    $parentChecked  = true;
                } else {
                    $item['active'] = false;
                }

                if (! isset($item['blank'])) {
                    $item['blank'] = false;
                }
                $link['children'][$key] = $item;
            }

            if (! $parentChecked && $this->checkParentActive($prefixes)) {
                $parentChecked = true;
            }

            // Check if this menu item should be kept
            if ($topRoute == 'dashboard.index') {
                $shouldKeep = true;
            } elseif ($link['has_children']) {
                $shouldKeep = $hasVisibleChild;
            } elseif ($link['skip_permission'] ?? false) {
                $shouldKeep = true;
            } else {
                $code       = str_replace('.', '_', $topRoute);
                $shouldKeep = $this->adminUser->can($code);
            }

            if ($shouldKeep) {
                // If there's a stored divider, add it first
                if ($lastDivider) {
                    $result[]    = $lastDivider;
                    $lastDivider = null;
                }

                if ($link['has_children']) {
                    $link['children'] = array_values($link['children']);
                }

                $result[] = $link;

                $result[count($result) - 1]['active'] = $parentChecked;
            }
        }

        return array_values($result);
    }

    /**
     * @param  $route
     * @return bool
     */
    private function checkChildActive($route): bool
    {
        if ($route == $this->currentRoute) {
            return true;
        }

        $routePart = substr($route, 0, strpos($route, '.'));
        if (empty($routePart)) {
            return false;
        }

        $currentPath = substr($this->currentRoute, 0, strpos($this->currentRoute, '.'));
        if ($routePart == $currentPath) {
            return true;
        }

        return false;
    }

    /**
     * @param  $prefixes
     * @return bool
     */
    private function checkParentActive($prefixes): bool
    {
        if ($prefixes && in_array($this->currentPrefix, $prefixes)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get product sub routes.
     */
    public function getOrderSubRoutes(): array
    {
        $routes = [
            ['route' => 'orders.index', 'title' => __('console/menu.orders')],
            ['route' => 'order_returns.index', 'title' => __('console/menu.order_returns')],
            ['route' => 'refunds.index', 'title' => __('console/menu.refunds')],
            ['route' => 'return_reasons.index', 'title' => __('console/menu.return_reasons')],
            ['route' => 'warehouses.index', 'title' => __('console/menu.warehouses')],
            ['route' => 'warehouse_stocks.index', 'title' => __('console/menu.warehouse_stocks')],
            ['route' => 'stock_transfers.index', 'title' => __('console/menu.stock_transfers')],
        ];

        return fire_hook_filter('console.component.sidebar.order.routes', $routes);
    }

    /**
     * Get product sub routes.
     */
    public function getProductSubRoutes(): array
    {
        $routes = [
            ['route' => 'products.index', 'title' => __('console/menu.products')],
            ['route' => 'categories.index', 'title' => __('console/menu.categories')],
            ['route' => 'brands.index', 'title' => __('console/menu.brands')],
            ['route' => 'attributes.index', 'title' => __('console/menu.attributes')],
            ['route' => 'options.index', 'title' => __('console/menu.options')],
            ['route' => 'reviews.index', 'title' => __('console/menu.reviews')],
        ];

        return fire_hook_filter('console.component.sidebar.product.routes', $routes);
    }

    /**
     * Get article sub routes
     */
    public function getCustomerSubRoutes(): array
    {
        $routes = [
            ['route' => 'customers.index', 'title' => __('console/menu.customers')],
            ['route' => 'customer_groups.index', 'title' => __('console/menu.customer_groups')],
            ['route' => 'member_levels.index', 'title' => __('console/menu.member_levels')],
            ['route' => 'point_logs.index', 'title' => __('console/menu.point_logs')],
            ['route' => 'transactions.index', 'title' => __('console/menu.transactions')],
            ['route' => 'withdrawals.index', 'title' => __('console/menu.withdrawals')],
            ['route' => 'socials.index', 'title' => __('console/menu.sns')],
        ];

        return fire_hook_filter('console.component.sidebar.customer.routes', $routes);
    }

    /**
     * Get article sub routes
     */
    public function getAnalyticSubRoutes(): array
    {
        $routes = [
            ['route' => 'analytics.index', 'title' => __('console/menu.analytics')],
            ['route' => 'analytics_order', 'title' => __('console/menu.analytics_order')],
            ['route' => 'analytics_product', 'title' => __('console/menu.analytics_product')],
            ['route' => 'analytics_customer', 'title' => __('console/menu.analytics_customer')],
            ['route' => 'reconciliation.index', 'title' => __('console/menu.reconciliation')],
            ['route' => 'visits.index', 'title' => __('console/menu.visits')],
            ['route' => 'visits.statistics', 'title' => __('console/menu.visits_statistics')],
        ];

        return fire_hook_filter('console.component.sidebar.analytic.routes', $routes);
    }

    /**
     * Get content sub routes
     */
    public function getContentSubRoutes(): array
    {
        $routes = [
            ['route' => 'articles.index', 'title' => __('console/menu.articles')],
            ['route' => 'catalogs.index', 'title' => __('console/menu.catalogs')],
            ['route' => 'tags.index', 'title' => __('console/menu.tags')],
            ['route' => 'pages.index', 'title' => __('console/menu.pages')],
            ['route' => 'file_manager.index', 'title' => __('console/menu.file_manager')],
        ];

        return fire_hook_filter('console.component.sidebar.content.routes', $routes);
    }

    /**
     * Get design sub routes.
     */
    public function getDesignSubRoutes(): array
    {
        $routes = [
            ['route' => 'themes_settings.index', 'title' => __('console/menu.themes_settings')],
            ['route' => 'themes.index', 'title' => __('console/menu.themes')],
        ];

        return fire_hook_filter('console.component.sidebar.design.routes', $routes);
    }

    /**
     * Get plugin sub routes.
     */
    public function getPluginSubRoutes(): array
    {
        $routes      = [];
        $currentType = $this->getCurrentPluginType();

        // Add "All Plugins" link
        $routes[] = [
            'route'  => 'plugins.index',
            'title'  => __('console/plugin.all'),
            'url'    => console_route('plugins.index'),
            'active' => $this->currentRoute === 'plugins.index' && ! request('type') && ! $currentType,
        ];

        $routes[] = [
            'route'           => 'plugin-market.index',
            'title'           => __('console/menu.plugin_market'),
            'skip_permission' => true,
        ];
        $routes[] = [
            'route'           => 'theme-market.index',
            'title'           => __('console/menu.theme_market'),
            'skip_permission' => true,
        ];

        // Add plugin type menus
        foreach (PluginTypeRepo::getInstance()->getTypeMenus() as $menu) {
            $type           = $menu['params']['type'] ?? null;
            $menu['active'] = ($this->currentRoute === 'plugins.index' && request('type') === $type) ||
                             ($currentType === $type);
            $routes[] = $menu;
        }

        return fire_hook_filter('console.component.sidebar.plugin.routes', $routes);
    }

    /**
     * Get current plugin type from route.
     */
    private function getCurrentPluginType(): ?string
    {
        if (! in_array($this->currentRoute, ['plugins.edit', 'plugins.show'])) {
            return null;
        }

        $pluginCode = request()->route('plugin');
        if (! $pluginCode) {
            return null;
        }

        try {
            $plugin = app('plugin')->getPlugin($pluginCode);

            return $plugin ? $plugin->getType() : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get setting sub routes.
     */
    public function getSettingSubRoutes(): array
    {
        $routes = [
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
            ['route' => 'shipping_zones.index', 'title' => __('console/menu.shipping_zones')],
            ['route' => 'shipping_templates.index', 'title' => __('console/menu.shipping_templates')],
            ['route' => 'redirects.index', 'title' => __('console/menu.redirects')],
            ['route' => 'legal_documents.index', 'title' => __('console/menu.legal_documents')],
            ['route' => 'gdpr_requests.index', 'title' => __('console/menu.gdpr_requests')],
            ['route' => 'backups.index', 'title' => __('console/menu.backups')],
            ['route' => 'health.index', 'title' => __('console/menu.health')],
            ['route' => 'schedule.index', 'title' => __('console/menu.schedule')],
            ['route' => 'system_update.index', 'title' => __('console/menu.system_update'), 'skip_permission' => true],
        ];

        return fire_hook_filter('console.component.sidebar.setting.routes', $routes);
    }

    /**
     * Get marketing sub routes.
     */
    public function getMarketingSubRoutes(): array
    {
        $routes = [
            ['route' => 'promotions.index', 'title' => __('console/menu.promotions')],
            ['route' => 'coupons.index', 'title' => __('console/menu.coupons')],
            ['route' => 'abandoned_carts.index', 'title' => __('console/menu.abandoned_carts')],
        ];

        return fire_hook_filter('console.component.sidebar.marketing.routes', $routes);
    }
}
