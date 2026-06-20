<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'zones'            => 'Shipping Zones',
    'zone_name'        => 'Zone Name',
    'country_ids'      => 'Country IDs',
    'country_ids_hint' => 'Comma-separated country IDs; leave blank for all countries',
    'state_ids'        => 'State IDs',
    'state_ids_hint'   => 'Comma-separated state IDs; leave blank for all within the zone',
    'priority'         => 'Priority',

    'templates'           => 'Shipping Templates',
    'template_name'       => 'Template Name',
    'zone'                => 'Shipping Zone',
    'all_zones'           => 'All Zones',
    'calc_type'           => 'Calculation Type',
    'calc_flat'           => 'Flat Rate',
    'calc_by_weight'      => 'By Weight',
    'calc_by_qty'         => 'By Quantity',
    'calc_by_amount'      => 'By Amount',
    'rules'               => 'Rules (JSON)',
    'rules_hint'          => 'e.g. {"base":5,"rate":2,"unit":1} or {"tiers":[{"max":1,"cost":5},{"max":5,"cost":12}]}',
    'free_threshold'      => 'Free Shipping Threshold',
    'free_threshold_hint' => 'Free shipping when subtotal reaches this amount; 0 = disabled',
];
