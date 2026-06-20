<?php
namespace Plugin\TaxEngine;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\TaxEngine\Services\TaxEngineFee;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('service.checkout.fee.methods', function (array $classes) {
            $classes[] = TaxEngineFee::class;

            return $classes;
        });

        listen_hook_filter('console.component.sidebar.setting.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'tax_engine.index',
                'title'           => __('TaxEngine::common.menu'),
                'url'             => console_route('tax_engine.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
