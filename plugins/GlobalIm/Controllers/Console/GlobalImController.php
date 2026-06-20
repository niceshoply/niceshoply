<?php
namespace Plugin\GlobalIm\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\GlobalIm\Models\ImMessage;
use Plugin\GlobalIm\Services\GlobalImService;

class GlobalImController extends BaseController
{
    public function index(): mixed
    {
        $messages = ImMessage::query()->orderByDesc('id')->limit(100)->get();

        return nice_view('GlobalIm::console.index', compact('messages'));
    }

    public function send(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'channel' => 'required|string|max:32',
                'peer_id' => 'required|string|max:128',
                'body'    => 'required|string|max:4096',
            ]);
            GlobalImService::getInstance()->send($data['channel'], $data['peer_id'], $data['body']);

            return json_success(__('GlobalIm::common.sent'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
