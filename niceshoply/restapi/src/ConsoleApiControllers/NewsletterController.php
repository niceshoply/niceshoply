<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\ConsoleApiControllers;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\NewsletterSubscriber;
use NiceShoply\Common\Repositories\NewsletterRepo;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Newsletter 订阅者后台 API 控制器
 *
 * 提供订阅者列表、新增、更新、删除、CSV 导出与统计。
 */
class NewsletterController extends BaseController
{
    /**
     * 订阅者列表。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $perPage = (int) $request->get('per_page', 15);

        $subscribers = NewsletterRepo::getInstance()
            ->builder($request->all())
            ->orderByDesc('id')
            ->paginate($perPage);

        return read_json_success($subscribers);
    }

    /**
     * 统计概览（总数、各状态计数）。
     *
     * @return mixed
     */
    public function statistics(): mixed
    {
        $counts = [];
        foreach (NewsletterSubscriber::STATUSES as $status) {
            $counts[$status] = NewsletterSubscriber::where('status', $status)->count();
        }

        return read_json_success([
            'total'  => NewsletterSubscriber::count(),
            'counts' => $counts,
        ]);
    }

    /**
     * 后台手动新增订阅者。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request): mixed
    {
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email',
            'name'  => 'nullable|string|max:255',
        ]);

        try {
            $subscriber = NewsletterSubscriber::create([
                'email'         => $request->input('email'),
                'name'          => $request->input('name'),
                'status'        => NewsletterSubscriber::STATUS_ACTIVE,
                'source'        => NewsletterSubscriber::SOURCE_MANUAL,
                'subscribed_at' => now(),
                'notes'         => $request->input('notes'),
            ]);

            return create_json_success($subscriber);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 更新订阅者（状态、备注等）。
     *
     * @param  NewsletterSubscriber  $newsletter
     * @param  Request  $request
     * @return mixed
     */
    public function update(NewsletterSubscriber $newsletter, Request $request): mixed
    {
        $request->validate([
            'status' => 'nullable|in:'.implode(',', NewsletterSubscriber::STATUSES),
            'name'   => 'nullable|string|max:255',
        ]);

        try {
            $data = $request->only(['name', 'status', 'notes']);

            // 状态切换时同步订阅/退订时间
            if (isset($data['status'])) {
                if ($data['status'] === NewsletterSubscriber::STATUS_ACTIVE && ! $newsletter->isActive()) {
                    $data['subscribed_at']   = now();
                    $data['unsubscribed_at'] = null;
                } elseif ($data['status'] === NewsletterSubscriber::STATUS_UNSUBSCRIBED) {
                    $data['unsubscribed_at'] = now();
                }
            }

            $newsletter->update($data);

            return update_json_success($newsletter->fresh());
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 删除订阅者。
     *
     * @param  NewsletterSubscriber  $newsletter
     * @return mixed
     */
    public function destroy(NewsletterSubscriber $newsletter): mixed
    {
        try {
            $newsletter->delete();

            return delete_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 导出订阅者为 CSV。
     *
     * @param  Request  $request
     * @return StreamedResponse
     */
    public function export(Request $request): StreamedResponse
    {
        $filters = $request->all();

        $fileName = 'newsletter_subscribers_'.date('YmdHis').'.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return response()->stream(function () use ($filters) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM，便于 Excel 正确识别中文
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['ID', 'Email', 'Name', 'Status', 'Source', 'Subscribed At']);

            NewsletterRepo::getInstance()
                ->builder($filters)
                ->orderByDesc('id')
                ->chunk(500, function ($subscribers) use ($handle) {
                    foreach ($subscribers as $subscriber) {
                        fputcsv($handle, [
                            $subscriber->id,
                            $subscriber->email,
                            $subscriber->name,
                            $subscriber->status,
                            $subscriber->source,
                            optional($subscriber->subscribed_at)->toDateTimeString(),
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }
}
