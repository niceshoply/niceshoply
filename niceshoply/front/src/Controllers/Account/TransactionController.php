<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Front\Controllers\Account;

use NiceShoply\Common\Repositories\Customer\TransactionRepo;
use NiceShoply\Common\Repositories\Customer\WithdrawalRepo;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;

class TransactionController extends BaseController
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $customer   = current_customer();
        $customerID = $customer->id;

        $filters = [
            'customer_id' => $customerID,
        ];

        $customer->syncBalance();
        $balance = $customer->balance;
        $frozen  = (new WithdrawalRepo)->getFrozenAmount($customerID);
        $data    = [
            'balance'      => $balance,
            'frozen'       => $frozen,
            'available'    => $balance - $frozen,
            'transactions' => TransactionRepo::getInstance()->list($filters),
        ];

        return nice_view('account.transactions_index', $data);
    }
}
