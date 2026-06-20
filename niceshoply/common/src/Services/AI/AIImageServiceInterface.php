<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\AI;

/**
 * AI 图片生成能力接口
 *
 * 支持图片生成的 AI 适配器实现该接口；不支持图片的适配器无需实现，
 * 由 AIServiceManager 在调用前进行能力检测。
 */
interface AIImageServiceInterface
{
    /**
     * 根据文本提示生成图片。
     *
     * @param  string  $prompt  图片描述
     * @param  array  $options  选项：size、quality、model、n 等
     * @return array{url?: string, b64_json?: string, model?: string} 生成结果
     */
    public function generateImage(string $prompt, array $options = []): array;
}
