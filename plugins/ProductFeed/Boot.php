<?php
namespace Plugin\ProductFeed;

use NiceShoply\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('console.component.sidebar.product.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'product_feed.index',
                'title'           => __('ProductFeed::common.menu'),
                'url'             => console_route('product_feed.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
