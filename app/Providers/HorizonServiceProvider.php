<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // 队列失败 Sentry 监控
        \Illuminate\Support\Facades\Queue::failing(function (\Illuminate\Queue\Events\JobFailed $event) {
            if (app()->bound('sentry')) {
                \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($event) {
                    $scope->setTag('queue.connection', $event->connectionName);
                    $scope->setTag('queue.job', $event->job->resolveName());
                    $scope->setContext('queue_failure', [
                        'connection' => $event->connectionName,
                        'job'        => $event->job->resolveName(),
                    ]);
                    \Sentry\captureException($event->exception);
                });
            }
        });
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            // 仅管理员可访问 Horizon 仪表盘
            return $user instanceof \NiceShoply\Common\Models\Admin;
        });
    }
}
