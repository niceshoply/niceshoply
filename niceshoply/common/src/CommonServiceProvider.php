<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use NiceShoply\Common\Components\Base;
use NiceShoply\Common\Components\Forms;
use NiceShoply\Common\Console\Commands;
use NiceShoply\Common\Services\StorageService;

class CommonServiceProvider extends ServiceProvider
{
    /**
     * config path.
     */
    private string $basePath = __DIR__.'/../';

    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(StorageService::class);
    }

    /**
     * Boot front service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        load_settings();
        $this->registerConfig();
        $this->registerMigrations();
        $this->registerCommands();
        $this->loadMailSettings();
        $this->loadViewComponents();
        $this->loadViewTemplates();
        $this->registerAuditHooks();
        $this->registerMemberHooks();
        $this->registerAbandonedCartHooks();
        $this->registerComplianceHooks();
        $this->registerScheduleMonitorHooks();
        $this->registerNotificationSubscriber();
    }

    /**
     * 注册统一通知事件订阅（队列失败、长等待等触发通知）。
     *
     * @return void
     */
    private function registerNotificationSubscriber(): void
    {
        \NiceShoply\Common\Services\Notification\NotificationEventSubscriber::register();
    }

    /**
     * Register audit log hooks via the eventy hook system.
     *
     * @return void
     */
    private function registerAuditHooks(): void
    {
        if (! config('activitylog.enabled', false)) {
            return;
        }

        $this->app->booted(function () {
            $this->bootAuditHooks();
        });
    }

    private function bootAuditHooks(): void
    {
        if (! function_exists('add_hook_action')) {
            return;
        }

        add_hook_action('service.state_machine.change_status.after', function ($data) {
            $order = $data['order'] ?? null;
            if (! $order) {
                return;
            }

            activity('order_status')
                ->performedOn($order)
                ->withProperties([
                    'new_status' => $data['status'] ?? '',
                    'comment'    => $data['comment'] ?? '',
                    'notify'     => $data['notify'] ?? false,
                ])
                ->log("Order #{$order->number} status changed to {$data['status']}");
        });
    }

    /**
     * 注册会员等级与积分 Hook / 事件。
     */
    private function registerMemberHooks(): void
    {
        $this->app->booted(function () {
            $this->bootMemberHooks();
        });
    }

    private function bootMemberHooks(): void
    {
        if (function_exists('listen_hook_filter')) {
            listen_hook_filter('model.sku.final_price', function ($data) {
                return \NiceShoply\Common\Services\Member\MemberLevelService::getInstance()->applyMemberPrice($data);
            }, 100);
        }

        if (function_exists('add_hook_action')) {
            add_hook_action('service.refund.succeeded', function ($data) {
                \NiceShoply\Common\Services\Member\PointService::getInstance()->handleRefundSucceeded($data);
            }, 30);
        }

        app('events')->listen(\NiceShoply\Common\Events\OrderPaid::class, function (\NiceShoply\Common\Events\OrderPaid $event) {
            try {
                \NiceShoply\Common\Services\Member\PointService::getInstance()->earnForPaidOrder($event->order);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('订单支付积分发放失败：'.$e->getMessage(), [
                    'order_id' => $event->order->id,
                ]);
            }
        });
    }

    /**
     * 弃购挽回：下单转化标记。
     */
    private function registerAbandonedCartHooks(): void
    {
        $this->app->booted(function () {
            app('events')->listen(\NiceShoply\Common\Events\OrderPlaced::class, function (\NiceShoply\Common\Events\OrderPlaced $event) {
                try {
                    \NiceShoply\Common\Services\AbandonedCart\AbandonedCartService::getInstance()->markConverted($event->order);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('弃购转化标记失败：'.$e->getMessage(), [
                        'order_id' => $event->order->id,
                    ]);
                }
            });
        });
    }

    /**
     * 合规风控：订单风险评估（OrderPlaced）。
     */
    private function registerComplianceHooks(): void
    {
        $this->app->booted(function () {
            app('events')->listen(\NiceShoply\Common\Events\OrderPlaced::class, function (\NiceShoply\Common\Events\OrderPlaced $event) {
                try {
                    \NiceShoply\Common\Services\Compliance\OrderRiskService::getInstance()
                        ->evaluateAndPersist($event->order);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('订单风险评估失败：'.$e->getMessage(), [
                        'order_id' => $event->order->id,
                    ]);
                }
            });
        });
    }

    /**
     * 计划任务监控：记录 schedule 执行历史。
     */
    private function registerScheduleMonitorHooks(): void
    {
        $this->app->booted(function () {
            \NiceShoply\Common\Services\Ops\ScheduleMonitorService::registerEventListeners();
        });
    }

    /**
     * Register config.
     *
     * @return void
     */
    private function registerConfig(): void
    {
        $this->mergeConfigFrom($this->basePath.'config/niceshoply.php', 'niceshoply');
        if (installed()) {
            Config::set('app.debug', system_setting('debug', false));
        }
    }

    /**
     * Register migrations.
     *
     * @return void
     */
    private function registerMigrations(): void
    {
        $this->loadMigrationsFrom($this->basePath.'database/migrations');
    }

    /**
     * Register common commands.
     *
     * @return void
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\UpdateCountries::class,
                Commands\UpdateStates::class,
                Commands\PublishFrontTheme::class,
                Commands\MigrateProductImages::class,
                Commands\NormalizeLocales::class,
                Commands\MigrateImagePaths::class,
                Commands\OrderComplete::class,
                Commands\VisitAggregate::class,
                Commands\GeoLite2Update::class,
                Commands\UpdateCurrencyRates::class,
                Commands\AbandonedCartScan::class,
                Commands\SeoGenerateSitemap::class,
                Commands\BackupRun::class,
            ]);
        }
    }

    /**
     * Load the email configuration, fetch values from the backend mail,
     * and override them in config/mail and config/services.
     * @return void
     */
    private function loadMailSettings(): void
    {
        $mailEngine = strtolower(system_setting('email_engine'));

        if (empty($mailEngine)) {
            return;
        }

        $storeMailAddress = system_setting('email', '');

        Config::set('mail.default', $mailEngine);
        Config::set('mail.from.address', $storeMailAddress);
        Config::set('mail.from.name', config('app.name'));

        if ($mailEngine == 'smtp') {
            Config::set('mail.mailers.smtp', [
                'transport'  => 'smtp',
                'host'       => system_setting('smtp_host'),
                'port'       => (int) system_setting('smtp_port', 587),
                'encryption' => strtolower(system_setting('smtp_encryption')),
                'username'   => system_setting('smtp_username'),
                'password'   => system_setting('smtp_password'),
                'timeout'    => (int) system_setting('smtp_timeout', 60),
            ]);
        }
    }

    /**
     * Load view components.
     *
     * @return void
     */
    protected function loadViewComponents(): void
    {
        // Base components
        $this->loadViewComponentsAs('common', [
            'alert'         => Base\Alert::class,
            'no-data'       => Base\NoData::class,
            'delete-button' => Base\DeleteButton::class,
        ]);

        // Form components
        $this->loadViewComponentsAs('common-form', [
            'input'        => Forms\Input::class,
            'select'       => Forms\Select::class,
            'textarea'     => Forms\Textarea::class,
            'rich-text'    => Forms\RichText::class,
            'image'        => Forms\Image::class,
            'imagep'       => Forms\ImagePure::class,
            'images'       => Forms\Images::class,
            'imagesp'      => Forms\ImagesPure::class,
            'file'         => Forms\File::class,
            'date'         => Forms\Date::class,
            'switch-radio' => Forms\SwitchRadio::class,
            'model-switch' => Forms\ModelSwitch::class,
        ]);
    }

    /**
     * Load templates
     *
     * @return void
     */
    private function loadViewTemplates(): void
    {
        $originViewPath = nice_path('common/resources/views');
        $customViewPath = resource_path('views/vendor/common');

        $this->publishes([
            $originViewPath => $customViewPath,
        ], 'views');

        $this->loadViewsFrom($originViewPath, 'common');
    }
}
