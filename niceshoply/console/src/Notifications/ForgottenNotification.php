<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NiceShoply\Common\Models\Admin;
use NiceShoply\Console\Mail\Forgotten;

class ForgottenNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Admin $admin;

    private string $code;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Admin $admin, string $code)
    {
        $this->admin = $admin;
        $this->code  = $code;
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
        $drivers[]  = 'database';
        $mailEngine = system_setting('email_engine');
        if ($mailEngine) {
            $drivers[] = 'mail';
        }

        return $drivers;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return Forgotten
     */
    public function toMail(mixed $notifiable): Forgotten
    {
        return (new Forgotten($this->code, $this->admin->email))
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
        return [
            //
        ];
    }

    /**
     * Save to DB
     * @return Admin[]
     */
    public function toDatabase(): array
    {
        return [
            'user' => $this->admin,
        ];
    }
}
