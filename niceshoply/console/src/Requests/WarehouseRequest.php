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

class WarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $warehouseId = $this->route('warehouse') ? $this->route('warehouse')->id : null;

        return [
            'code'                       => 'required|string|max:64|unique:warehouses,code,'.$warehouseId,
            'name'                       => 'required|string|max:128',
            'contact_name'               => 'nullable|string|max:64',
            'contact_phone'              => 'nullable|string|max:32',
            'country_id'                 => 'nullable|integer',
            'country'                    => 'nullable|string|max:64',
            'state_id'                   => 'nullable|integer',
            'state'                      => 'nullable|string|max:64',
            'city'                       => 'nullable|string|max:64',
            'address_1'                  => 'nullable|string|max:255',
            'address_2'                  => 'nullable|string|max:255',
            'zipcode'                    => 'nullable|string|max:16',
            'priority'                   => 'nullable|integer|min:0',
            'service_areas'              => 'nullable|array',
            'service_areas.*.country_id' => 'required_with:service_areas|integer|min:1',
            'service_areas.*.state_id'   => 'nullable|integer|min:0',
        ];
    }
}
