<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Refund;

/**
 * 网关退款结果值对象。
 */
final class RefundResult
{
    /**
     * @param  bool  $success  是否成功
     * @param  string  $reference  网关退款流水号
     * @param  string  $message  失败原因或备注
     * @param  array  $context  网关原始返回（落库到流水）
     */
    public function __construct(
        public bool $success,
        public string $reference = '',
        public string $message = '',
        public array $context = [],
    ) {}

    /**
     * 构造成功结果。
     *
     * @param  string  $reference
     * @param  array  $context
     * @return self
     */
    public static function success(string $reference = '', array $context = []): self
    {
        return new self(true, $reference, '', $context);
    }

    /**
     * 构造失败结果。
     *
     * @param  string  $message
     * @param  array  $context
     * @return self
     */
    public static function failure(string $message, array $context = []): self
    {
        return new self(false, '', $message, $context);
    }
}
