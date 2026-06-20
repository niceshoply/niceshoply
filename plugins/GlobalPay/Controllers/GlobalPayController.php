<?php
namespace Plugin\GlobalPay\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Repositories\Order\PaymentRepo;
use NiceShoply\Common\Repositories\OrderRepo;
use NiceShoply\Common\Services\StateMachineService;

class GlobalPayController extends Controller
{
    public function notify(Request $request)
    {
        try {
            if ((string) plugin_setting('global_pay', 'provider', 'stripe') === 'stripe') {
                return $this->handleStripeWebhook($request);
            }

            return $this->handlePayPalReturn($request);
        } catch (\Throwable $e) {
            Log::channel('payment')->error('global_pay.notify.failed', ['error' => $e->getMessage()]);

            return response('fail', 500);
        }
    }

    protected function handleStripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $secret  = (string) plugin_setting('global_pay', 'stripe_webhook_secret', '');
        if ($secret === '') {
            return response('secret missing', 400);
        }

        $sigHeader = (string) $request->header('Stripe-Signature');
        if (! $this->verifyStripeSignature($payload, $sigHeader, $secret)) {
            return response('invalid signature', 400);
        }

        $event = json_decode($payload, true);
        $type  = $event['type'] ?? '';
        if (! in_array($type, ['checkout.session.completed', 'payment_intent.succeeded'], true)) {
            return response('ok');
        }

        $object      = $event['data']['object'] ?? [];
        $orderNumber = (string) ($object['client_reference_id'] ?? ($object['metadata']['order_number'] ?? ''));

        return $this->markPaid($orderNumber, $object);
    }

    protected function handlePayPalReturn(Request $request)
    {
        $orderNumber = (string) $request->input('reference_id', $request->input('token', ''));

        return $this->markPaid($orderNumber, $request->all());
    }

    protected function markPaid(string $orderNumber, array $reference)
    {
        if ($orderNumber === '') {
            return response('ok');
        }

        $order = OrderRepo::getInstance()->getOrderByNumber($orderNumber);
        if (! $order) {
            return response('ok');
        }

        if (in_array($order->status, StateMachineService::getValidStatuses(), true)) {
            return response('ok');
        }

        PaymentRepo::getInstance()->createOrUpdatePayment($order->id, [
            'charge_id' => $reference['id'] ?? $orderNumber,
            'amount'    => $order->total,
            'paid'      => true,
            'reference' => $reference,
        ]);

        StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

        return response('ok');
    }

    protected function verifyStripeSignature(string $payload, string $sigHeader, string $secret): bool
    {
        if ($sigHeader === '') {
            return false;
        }
        $parts = [];
        foreach (explode(',', $sigHeader) as $item) {
            [$k, $v] = array_pad(explode('=', trim($item), 2), 2, null);
            if ($k && $v) {
                $parts[$k] = $v;
            }
        }
        $timestamp = $parts['t'] ?? '';
        $signature = $parts['v1'] ?? '';
        if ($timestamp === '' || $signature === '') {
            return false;
        }
        $signed = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        return hash_equals($signed, $signature);
    }
}
