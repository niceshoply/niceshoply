<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Refund;

use NiceShoply\Common\Models\Refund;

/**
 * 原路退款契约。
 *
 * 支付插件（Stripe/PayPal 等）实现本接口，使其支持通过原支付渠道发起退款。
 * RefundService 在 method=original 时按订单支付网关解析对应实现并调用 refund()。
 */
interface RefundableInterface
{
    /**
     * 支付网关唯一标识（与订单 payment_method_code / Refund.gateway 对应）。
     *
     * @return string
     */
    public function gatewayCode(): string;

    /**
     * 通过原支付渠道发起退款。
     *
     * 实现需保证幂等（同一退款单重复调用不应重复退款），
     * 成功返回网关退款流水号，失败抛出异常或返回带 success=false 的结果。
     *
     * @param  Refund  $refund  退款单
     * @return RefundResult
     */
    public function refund(Refund $refund): RefundResult;
}
