<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Requests\Concerns;

/**
 * PATCH 部分更新请求校验 Trait。
 *
 * RESTful 语义中：
 *  - PUT  = 整体替换：所有字段都应提交并校验；
 *  - PATCH= 部分更新：仅提交需要修改的字段，未提交的字段保持原值、不参与校验。
 *
 * Laravel `Route::resource` 的 update 同时响应 PUT 与 PATCH，
 * 本 Trait 让同一个 FormRequest 在 PATCH 下自动只校验「请求中实际出现」的字段，
 * 从而支持部分更新而不触发缺省必填字段的校验失败。
 *
 * 用法：在 FormRequest 的 rules() 末尾用 `$this->patchRules($rules)` 包裹返回值。
 */
trait PatchRequestTrait
{
    /**
     * 针对 PATCH 请求过滤校验规则，仅保留请求中出现的字段。
     *
     * 非 PATCH 请求（POST/PUT）原样返回完整规则，保证既有行为不变。
     *
     * @param  array  $rules  完整校验规则
     * @return array 经 PATCH 过滤后的规则
     */
    protected function patchRules(array $rules): array
    {
        if (! $this->isPatch()) {
            return $rules;
        }

        return array_filter(
            $rules,
            function (string $attribute): bool {
                // 支持点记法嵌套字段（如 items.*.qty）：按根字段判断是否出现
                $root = explode('.', $attribute)[0];

                return $this->has($root) || $this->hasFile($root);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * 当前请求是否为 PATCH（部分更新）。
     *
     * @return bool
     */
    public function isPatch(): bool
    {
        return $this->isMethod('PATCH');
    }
}
