<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NiceShoply\Common\Models\AbandonedCart;
use NiceShoply\Front\Mail\AbandonedCartMail;

/**
 * 弃购召回邮件通知。
 */
class AbandonedCartNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private AbandonedCart $abandonedCart)
    {
        $this->onQueue('notifications');
    }

    public function via(mixed $notifiable): array
    {
        $drivers = [];

        $mailEngine    = system_setting('email_engine');
        $notifications = system_setting('email_notifications', []);

        if ($mailEngine && in_array('abandoned_cart', $notifications, true)) {
            $drivers[] = 'mail';
        }

        return $drivers;
    }

    public function toMail(mixed $notifiable): AbandonedCartMail
    {
        return (new AbandonedCartMail($this->abandonedCart))
            ->to($notifiable->email);
    }
}
