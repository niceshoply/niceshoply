<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Front\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Address;
use NiceShoply\Common\Repositories\AddressRepo;
use NiceShoply\Common\Resources\AddressListItem;
use Throwable;

class AddressesController extends Controller
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $filters = [
            'customer_id' => current_customer_id(),
        ];
        $items     = AddressRepo::getInstance()->builder($filters)->get();
        $addresses = (AddressListItem::collection($items))->jsonSerialize();

        $data = [
            'addresses' => $addresses,
        ];

        return nice_view('account.addresses', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    public function store(Request $request): mixed
    {
        $data                = $request->all();
        $data['customer_id'] = current_customer_id();

        $address = AddressRepo::getInstance()->create($data);
        $result  = new AddressListItem($address);

        return create_json_success($result);
    }

    /**
     * @param  Request  $request
     * @param  Address  $address
     * @return mixed
     */
    public function update(Request $request, Address $address): mixed
    {
        $currentCustomerId = current_customer_id();

        if ($address->customer_id !== $currentCustomerId) {
            return json_fail('Unauthorized', null, 403);
        }

        $data                = $request->all();
        $data['customer_id'] = $currentCustomerId;

        $address = AddressRepo::getInstance()->update($address, $data);
        $result  = new AddressListItem($address);

        return update_json_success($result);
    }

    /**
     * @param  Address  $address
     * @return mixed
     */
    public function destroy(Address $address): mixed
    {
        $currentCustomerId = current_customer_id();

        if ($address->customer_id !== $currentCustomerId) {
            return json_fail('Unauthorized', null, 403);
        }

        AddressRepo::getInstance()->destroy($address);

        return delete_json_success();
    }
}
