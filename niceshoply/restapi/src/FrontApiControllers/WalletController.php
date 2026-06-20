<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\FrontApiControllers;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Customer\Transaction;

class WalletController extends BaseController
{
    /**
     * Get transaction list for current customer.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function transactions(Request $request): mixed
    {
        try {
            $customer = $request->user();

            $transactions = Transaction::query()
                ->where('customer_id', $customer->id)
                ->orderByDesc('id')
                ->paginate($request->get('per_page', 15));

            return read_json_success($transactions);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Request a withdrawal.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function withdraw(Request $request): mixed
    {
        try {
            $request->validate([
                'amount'       => 'required|numeric|min:0.01',
                'bank_account' => 'required|string|max:255',
            ]);

            $customer = $request->user();
            $amount   = (float) $request->get('amount');

            if ($amount > $customer->balance) {
                throw new Exception('Insufficient balance');
            }

            // Create withdrawal transaction
            $transaction = Transaction::create([
                'customer_id' => $customer->id,
                'amount'      => -$amount,
                'type'        => Transaction::TYPE_WITHDRAW,
                'comment'     => 'Withdrawal to '.$request->get('bank_account'),
                'balance'     => $customer->balance - $amount,
            ]);

            // Update customer balance
            $customer->balance -= $amount;
            $customer->save();

            return create_json_success($transaction);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
