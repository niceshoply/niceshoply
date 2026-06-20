<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Shipping;

use NiceShoply\Common\Entities\ShippingEntity;
use NiceShoply\Common\Models\ShippingTemplate;
use NiceShoply\Common\Repositories\ShippingTemplateRepo;
use NiceShoply\Common\Repositories\ShippingZoneRepo;

/**
 * 内置运费报价服务（quote provider）。
 *
 * 依据目的地匹配配送区域，再按各运费模板的计费方式（固定/按重量/按件数/按金额）
 * 计算运费，并应用满额包邮。输出与配送插件一致的 quote 结构，由
 * Checkout\ShippingService::getMethods() 合并到配送方式列表。
 */
final class ShippingTemplateService
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new self;
    }

    /**
     * 计算可用的内置运费报价。
     *
     * @param  ShippingEntity  $entity
     * @return array<int, array<string, mixed>> quote 列表：code/name/cost/cost_format
     */
    public function getQuotes(ShippingEntity $entity): array
    {
        if (! system_setting('shipping_template_enabled', true)) {
            return [];
        }

        $dest      = $entity->getDestAddress();
        $countryId = (int) ($dest['country_id'] ?? 0);
        $stateId   = (int) ($dest['state_id'] ?? 0);

        // 匹配命中的区域（优先级降序，命中即取全部匹配区域 ID）
        $zoneIds = [];
        foreach (ShippingZoneRepo::getInstance()->getActiveOrdered() as $zone) {
            if ($zone->matches($countryId, $stateId)) {
                $zoneIds[] = $zone->id;
            }
        }

        $templates = ShippingTemplateRepo::getInstance()->getActiveByZones($zoneIds);

        $subtotal = $entity->getSubtotal();
        $weight   = $entity->getWeight();
        $quantity = (int) collect($entity->getProducts())->sum('quantity');

        $quotes = [];
        foreach ($templates as $template) {
            $cost = $this->calculateCost($template, $subtotal, $weight, $quantity);

            $quotes[] = [
                'code'        => 'nice_tpl_'.$template->id,
                'name'        => $template->name,
                'cost'        => $cost,
                'cost_format' => currency_format($cost),
            ];
        }

        return fire_hook_filter('service.shipping.template.quotes', $quotes);
    }

    /**
     * 按模板计费方式计算运费（含满额包邮）。
     *
     * @param  ShippingTemplate  $template
     * @param  float  $subtotal
     * @param  float  $weight
     * @param  int  $quantity
     * @return float
     */
    public function calculateCost(ShippingTemplate $template, float $subtotal, float $weight, int $quantity): float
    {
        // 满额包邮
        $freeThreshold = (float) $template->free_threshold;
        if ($freeThreshold > 0 && $subtotal >= $freeThreshold) {
            return 0.0;
        }

        $rules = $template->rules ?? [];
        $base  = (float) ($rules['base'] ?? 0);

        $cost = match ($template->calc_type) {
            'by_weight' => $base + $this->tieredOrRate($rules, $weight),
            'by_qty'    => $base + $this->tieredOrRate($rules, $quantity),
            'by_amount' => $base + $this->tieredOrRate($rules, $subtotal),
            default     => $base, // flat
        };

        return round(max(0.0, $cost), currency_decimal_place());
    }

    /**
     * 阶梯或单位费率计算。
     *
     * 规则支持两种形式：
     *  - tiers: [['max'=>.., 'cost'=>..], ...]，取首个 value<=max 的档位 cost（未命中用最后一档）；
     *  - rate + unit: 费率 * ceil(value / unit)。
     *
     * @param  array  $rules
     * @param  float  $value
     * @return float
     */
    private function tieredOrRate(array $rules, float $value): float
    {
        $tiers = $rules['tiers'] ?? [];
        if (! empty($tiers)) {
            $sorted = collect($tiers)->sortBy(fn ($t) => (float) ($t['max'] ?? 0))->values();
            foreach ($sorted as $tier) {
                if ($value <= (float) ($tier['max'] ?? 0)) {
                    return (float) ($tier['cost'] ?? 0);
                }
            }

            return (float) ($sorted->last()['cost'] ?? 0);
        }

        $rate = (float) ($rules['rate'] ?? 0);
        $unit = (float) ($rules['unit'] ?? 1);
        if ($rate > 0 && $unit > 0) {
            return $rate * (float) ceil($value / $unit);
        }

        return 0.0;
    }
}
