<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    NiceShoply\Common\CommonServiceProvider::class,
    NiceShoply\Console\ConsoleServiceProvider::class,
    NiceShoply\Front\FrontServiceProvider::class,
    NiceShoply\Install\InstallServiceProvider::class,
    NiceShoply\Plugin\PluginServiceProvider::class,
    NiceShoply\RestAPI\RestAPIServiceProvider::class,
];
