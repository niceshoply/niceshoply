<?php
namespace Plugin\GlobalIm;

use NiceShoply\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'global_im.index',
                'title'           => __('GlobalIm::common.menu'),
                'url'             => console_route('global_im.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
