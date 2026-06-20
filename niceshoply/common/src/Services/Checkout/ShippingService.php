<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Checkout;

use Exception;
use Illuminate\Support\Str;
use NiceShoply\Common\Entities\ShippingEntity;
use NiceShoply\Common\Services\CheckoutService;
use NiceShoply\Plugin\Core\Plugin;
use NiceShoply\Plugin\Repositories\PluginRepo;
use Throwable;

class ShippingService
{
    public static ?array $shippingMethods = null;

    protected ?CheckoutService $checkoutService;

    protected ?ShippingEntity $shippingEntity;

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * @param  CheckoutService  $checkoutService
     * @return ShippingService
     * @throws Throwable
     */
    public function setCheckoutService(CheckoutService $checkoutService): static
    {
        $this->checkoutService = $checkoutService;
        $this->setShippingEntity(ShippingEntity::getInstance()->setCheckoutService($checkoutService));

        return $this;
    }

    /**
     * @param  ShippingEntity  $entity
     * @return static
     */
    public function setShippingEntity(ShippingEntity $entity): static
    {
        $this->shippingEntity = $entity;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function getMethods(): array
    {
        if (! is_null(self::$shippingMethods)) {
            return self::$shippingMethods;
        }

        $shippingPlugins = PluginRepo::getInstance()->getShippingMethods();

        $shippingMethods = [];
        foreach ($shippingPlugins as $shippingPlugin) {
            $plugin = $shippingPlugin->plugin;

            $bootClass = $this->getBootClass($plugin);
            if (! method_exists($bootClass, 'getQuotes')) {
                throw new Exception(front_trans('checkout.shipping_quote_error', ['classname' => $bootClass]));
            }

            $quotes = (new $bootClass)->getQuotes($this->shippingEntity);

            if ($quotes) {
                $shippingMethods[] = [
                    'code'   => $plugin->getCode(),
                    'name'   => $plugin->getLocaleName(),
                    'quotes' => $quotes,
                ];
            }
        }

        // 合并内置运费模板报价（与插件 quotes 并列展示）
        $templateQuotes = \NiceShoply\Common\Services\Shipping\ShippingTemplateService::getInstance()->getQuotes($this->shippingEntity);
        if ($templateQuotes) {
            $shippingMethods[] = [
                'code'   => 'niceshoply_shipping',
                'name'   => front_trans('checkout.shipping_methods'),
                'quotes' => $templateQuotes,
            ];
        }

        $shippingMethods = fire_hook_filter('common.service.checkout.shipping.methods', $shippingMethods);

        return self::$shippingMethods = $shippingMethods;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getQuoteCodes(): array
    {
        $quoteCodes      = [];
        $shippingMethods = $this->getMethods();
        foreach ($shippingMethods as $shippingMethod) {
            foreach ($shippingMethod['quotes'] as $quote) {
                $quoteCodes[] = $quote['code'];
            }
        }

        return $quoteCodes;
    }

    /**
     * @param  Plugin  $shippingPlugin
     * @return string
     */
    private function getBootClass(Plugin $shippingPlugin): string
    {
        $pluginCode = $shippingPlugin->getCode();
        $pluginName = Str::studly($pluginCode);

        return "Plugin\\{$pluginName}\\Boot";
    }
}
