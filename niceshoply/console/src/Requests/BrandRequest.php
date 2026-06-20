<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Requests;

use Illuminate\Foundation\Http\FormRequest;
use NiceShoply\Console\Requests\Concerns\PatchRequestTrait;

class BrandRequest extends FormRequest
{
    use PatchRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        if ($this->brand) {
            $slugRule = 'nullable|regex:/^[a-zA-Z0-9-]+$/|unique:brands,slug,'.$this->brand->id;
        } else {
            $slugRule = 'nullable|regex:/^[a-zA-Z0-9-]+$/|unique:brands,slug';
        }

        // PATCH 部分更新时仅校验请求中出现的字段；POST/PUT 校验完整规则。
        return $this->patchRules([
            'name'   => 'string|required|max:32',
            'slug'   => $slugRule,
            'first'  => 'string|required',
            'logo'   => 'required',
            'active' => 'bool',
        ]);
    }
}
