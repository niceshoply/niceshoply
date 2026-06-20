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

class CountryRequest extends FormRequest
{
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
        if ($this->country) {
            $slugRule = 'alpha_dash|unique:countries,code,'.$this->country->id;
        } else {
            $slugRule = 'alpha_dash|unique:countries,code';
        }

        return [
            'name'      => 'string|required|max:32',
            'code'      => $slugRule,
            'continent' => 'string|required',
            'position'  => 'integer',
            'active'    => 'bool',
        ];
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name'      => console_trans('common.name'),
            'code'      => console_trans('common.code'),
            'continent' => console_trans('country.continent'),
            'position'  => console_trans('common.position'),
        ];
    }
}
