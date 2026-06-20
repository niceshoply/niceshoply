<?php
namespace Plugin\GlobalIm\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Plugin\GlobalIm\Services\GlobalImService;

class WebhookController extends Controller
{
    public function telegram(Request $request)
    {
        GlobalImService::getInstance()->handleTelegramWebhook($request->all());

        return response('ok');
    }

    public function whatsapp(Request $request)
    {
        $verify = (string) plugin_setting('global_im', 'webhook_verify_token', '');
        if ($request->isMethod('get') && $request->input('hub_mode') === 'subscribe') {
            if ($verify !== '' && $request->input('hub_verify_token') === $verify) {
                return response($request->input('hub_challenge'));
            }

            return response('forbidden', 403);
        }

        GlobalImService::getInstance()->handleWhatsAppWebhook($request->all());

        return response('ok');
    }
}
