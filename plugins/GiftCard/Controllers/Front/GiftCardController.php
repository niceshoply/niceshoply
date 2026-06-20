<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\GiftCard\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\GiftCard\Models\GiftCard;
use Plugin\GiftCard\Services\GiftCardService;

class GiftCardController extends BaseController
{
    public function redeem(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'code' => 'required|string|max:32',
                'pin'  => 'required|string|max:32',
            ]);

            $amount = GiftCardService::getInstance()->redeem(
                (int) token_customer_id(),
                $data['code'],
                $data['pin']
            );

            return json_success(__('GiftCard::common.redeem_success', ['amount' => currency_format($amount)]), ['amount' => $amount]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function myCards(): mixed
    {
        $cards = GiftCard::query()
            ->where('customer_id', (int) token_customer_id())
            ->orderByDesc('redeemed_at')
            ->paginate(20);

        return json_success('ok', $cards);
    }
}
