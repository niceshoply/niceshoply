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
use NiceShoply\Common\Models\NewsletterSubscriber;
use NiceShoply\Common\Services\NewsletterService;

/**
 * 前台 Newsletter 订阅 API 控制器
 */
class NewsletterController extends BaseController
{
    /**
     * 订阅 Newsletter。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function subscribe(Request $request): mixed
    {
        $request->validate([
            'email'  => 'required|email|max:255',
            'name'   => 'nullable|string|max:255',
            'source' => 'nullable|in:'.implode(',', NewsletterSubscriber::SOURCES),
        ]);

        try {
            $subscriber = NewsletterService::getInstance()->subscribe([
                'email'  => $request->input('email'),
                'name'   => $request->input('name'),
                'source' => $request->input('source', NewsletterSubscriber::SOURCE_FOOTER),
            ]);

            return create_json_success(['id' => $subscriber->id, 'email' => $subscriber->email]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 退订 Newsletter。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function unsubscribe(Request $request): mixed
    {
        $request->validate(['email' => 'required|email']);

        $result = NewsletterService::getInstance()->unsubscribe($request->input('email'));

        return $result
            ? update_json_success(['email' => $request->input('email')])
            : json_fail(trans('front/common.failed'));
    }
}
