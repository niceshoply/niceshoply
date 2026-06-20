<?php
namespace Plugin\ProductFeed\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\ProductFeed\Models\FeedLog;
use Plugin\ProductFeed\Services\ProductFeedService;

class ProductFeedController extends BaseController
{
    public function index(): mixed
    {
        $logs = FeedLog::query()->orderByDesc('id')->limit(20)->get();

        return nice_view('ProductFeed::console.index', compact('logs'));
    }

    public function generate(Request $request): mixed
    {
        try {
            $channel = (string) $request->input('channel', 'google');
            $log     = ProductFeedService::getInstance()->generate($channel);

            return json_success(__('ProductFeed::common.generated', ['count' => $log->item_count]), [
                'url' => ProductFeedService::getInstance()->publicUrl($log),
            ]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
