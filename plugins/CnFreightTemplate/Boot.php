<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CnFreightTemplate;

use NiceShoply\Common\Entities\ShippingEntity;
use NiceShoply\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    public function init(): void {}

    public function getQuotes(ShippingEntity $entity): array
    {
        $code     = $this->plugin->getCode();
        $resource = $this->pluginResource->jsonSerialize();
        $cost     = $this->getShippingFee($entity);

        return [
            [
                'type'        => 'shipping',
                'code'        => "{$code}.0",
                'name'        => $resource['name'],
                'description' => $resource['description'] ?? '',
                'icon'        => $resource['icon'] ?? '',
                'cost'        => $cost,
                'cost_format' => currency_format($cost),
            ],
        ];
    }

    public function getShippingFee(ShippingEntity $entity): float
    {
        $code = 'cn_freight_template';

        // 满额包邮
        $freeThreshold = (float) plugin_setting($code, 'free_threshold', 0);
        if ($freeThreshold > 0 && $entity->getSubtotal() >= $freeThreshold) {
            return 0;
        }

        $mode = plugin_setting($code, 'charge_mode', 'weight');
        $fee  = match ($mode) {
            'piece'  => $this->calcByPiece($entity),
            'amount' => (float) plugin_setting($code, 'flat_fee', 0),
            default  => $this->calcByWeight($entity),
        };

        // 偏远地区加价
        $fee += $this->remoteSurcharge($entity);

        return round(max($fee, 0), 2);
    }

    private function calcByWeight(ShippingEntity $entity): float
    {
        $code        = 'cn_freight_template';
        $weight      = max($entity->getWeight(), 0);
        $firstWeight = max((float) plugin_setting($code, 'first_weight', 1), 0.001);
        $firstFee    = (float) plugin_setting($code, 'first_fee', 0);
        $extraUnit   = max((float) plugin_setting($code, 'extra_unit', 1), 0.001);
        $extraFee    = (float) plugin_setting($code, 'extra_fee', 0);

        if ($weight <= $firstWeight) {
            return $firstFee;
        }

        $extraUnits = ceil(($weight - $firstWeight) / $extraUnit);

        return $firstFee + $extraUnits * $extraFee;
    }

    private function calcByPiece(ShippingEntity $entity): float
    {
        $code       = 'cn_freight_template';
        $qty        = (int) collect($entity->getProducts())->sum('quantity');
        $firstPiece = max((int) plugin_setting($code, 'first_piece', 1), 1);
        $firstFee   = (float) plugin_setting($code, 'first_piece_fee', 0);
        $extraFee   = (float) plugin_setting($code, 'extra_piece_fee', 0);

        if ($qty <= $firstPiece) {
            return $firstFee;
        }

        return $firstFee + ($qty - $firstPiece) * $extraFee;
    }

    private function remoteSurcharge(ShippingEntity $entity): float
    {
        $surcharge = (float) plugin_setting('cn_freight_template', 'remote_surcharge', 0);
        if ($surcharge <= 0) {
            return 0;
        }

        $keywords = (string) plugin_setting('cn_freight_template', 'remote_keywords', '');
        if ($keywords === '') {
            return 0;
        }

        $address = $entity->getDestAddress();
        $haystack = implode(' ', array_filter([
            $address['state_name'] ?? ($address['state'] ?? ''),
            $address['city'] ?? '',
            $address['region_name'] ?? '',
        ]));

        foreach (array_filter(array_map('trim', explode(',', $keywords))) as $kw) {
            if ($kw !== '' && mb_strpos($haystack, $kw) !== false) {
                return $surcharge;
            }
        }

        return 0;
    }
}
