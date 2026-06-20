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
use NiceShoply\Common\Models\OrderReturn;

class OrderReturnUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private OrderReturn $orderReturn;

    private string $fromCode;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(OrderReturn $orderReturn, string $fromCode = '')
    {
        $this->orderReturn = $orderReturn;
        $this->fromCode    = $fromCode;
        $this->onQueue('mails');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $locale = $this->orderReturn->customer?->locale;
        if ($locale) {
            App::setLocale($locale);
        }

        return $this->view('mails.return_update', [
            'order_return' => $this->orderReturn,
            'from_code'    => $this->fromCode,
        ]);
    }
}
