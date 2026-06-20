<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NiceShoply\Common\Models\OrderReturn;
use NiceShoply\Front\Mail\OrderReturnUpdateMail;

class OrderReturnUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private OrderReturn $orderReturn;

    private string $fromCode;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(OrderReturn $orderReturn, string $fromCode = '')
    {
        $this->orderReturn = $orderReturn;
        $this->fromCode    = $fromCode;
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        $drivers[] = 'database';

        $mailEngine    = system_setting('email_engine');
        $notifications = system_setting('email_notifications', []);

        if ($mailEngine && in_array('return_status_update', $notifications)) {
            $drivers[] = 'mail';
        }

        return $drivers;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return OrderReturnUpdateMail
     */
    public function toMail(mixed $notifiable): OrderReturnUpdateMail
    {
        return (new OrderReturnUpdateMail($this->orderReturn, $this->fromCode))
            ->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray(mixed $notifiable): array
    {
        return [];
    }

    /**
     * Save to DB.
     *
     * @return array
     */
    public function toDatabase(): array
    {
        return [
            'order_return' => $this->orderReturn,
        ];
    }
}
