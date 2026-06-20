<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Front\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use NiceShoply\Common\Models\AbandonedCart;

class AbandonedCartMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(private AbandonedCart $abandonedCart)
    {
        $this->onQueue('mails');
    }

    public function build(): static
    {
        $cartUrl = function_exists('front_route') ? front_route('carts.index') : url('/');

        return $this->subject(trans('front/abandoned_cart.mail_subject'))
            ->view('mails.abandoned_cart', [
                'abandonedCart' => $this->abandonedCart,
                'cartUrl'       => $cartUrl,
            ]);
    }
}
