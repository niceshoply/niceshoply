<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Front\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use NiceShoply\Common\Models\Order;

class OrderUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private Order $order;

    private string $fromCode;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order, string $fromCode)
    {
        $this->order    = $order;
        $this->fromCode = $fromCode;
        $this->onQueue('mails');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $orderLocale = $this->order->locale;
        App::setLocale($orderLocale);

        return $this->view('mails.order_update', [
            'order'     => $this->order,
            'from_code' => $this->fromCode,
        ]);
    }
}
