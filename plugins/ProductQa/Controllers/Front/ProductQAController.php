<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ProductQa\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\ProductQa\Services\ProductQAService;

class ProductQAController extends BaseController
{
    public function index(Request $request): mixed
    {
        $productId = (int) $request->get('product_id');

        return json_success('ok', ProductQAService::getInstance()->listForProduct($productId));
    }

    public function ask(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'product_id' => 'required|integer|min:1',
                'content'    => 'required|string|max:500',
            ]);

            $question = ProductQAService::getInstance()->ask(
                (int) token_customer_id(),
                (int) $data['product_id'],
                $data['content']
            );

            return json_success(__('ProductQa::common.ask_submitted'), ['id' => $question->id, 'status' => $question->status]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function answer(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'question_id' => 'required|integer|min:1',
                'content'     => 'required|string|max:500',
            ]);

            $answer = ProductQAService::getInstance()->answer(
                (int) token_customer_id(),
                (int) $data['question_id'],
                $data['content'],
                false
            );

            return json_success(__('ProductQa::common.answer_submitted'), ['id' => $answer->id, 'status' => $answer->status]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
