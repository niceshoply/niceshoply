<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;
use NiceShoply\Common\Models\Admin;

/**
 * 生成命令行一次性后台登录链接（运维免密登录）。
 *
 * 用法：
 *   php artisan admin:cli-login                  # 默认取 id 最小（root）账号
 *   php artisan admin:cli-login admin@xx.com     # 指定邮箱账号
 *   php artisan admin:cli-login --minutes=30     # 自定义有效期（分钟）
 *
 * 输出一条带签名的临时 URL，浏览器打开即可免密进入后台。
 */
class CliLogin extends Command
{
    protected $signature = 'admin:cli-login {email? : 后台账号邮箱（缺省取 id 最小账号）} {--minutes=15 : 链接有效期（分钟）}';

    protected $description = 'Generate a signed one-time login link to access the admin console from CLI';

    public function handle(): int
    {
        $email = $this->argument('email');

        $admin = $email
            ? Admin::query()->where('email', $email)->first()
            : Admin::query()->orderBy('id')->first();

        if (! $admin) {
            $this->error($email
                ? "未找到邮箱为 {$email} 的后台账号。"
                : '不存在任何后台账号，请先执行 `php artisan db:seed`。');

            return self::FAILURE;
        }

        if (! $admin->active) {
            $this->error("账号 {$admin->email} 已被禁用，无法生成登录链接。");

            return self::FAILURE;
        }

        $minutes = max(1, (int) $this->option('minutes'));

        // 生成带 APP_KEY 签名、限定有效期的临时链接
        $url = URL::temporarySignedRoute(
            console_name().'.cli_login',
            now()->addMinutes($minutes),
            ['admin' => $admin->id]
        );

        $this->info("已为 {$admin->email} 生成一次性登录链接（{$minutes} 分钟内有效）：");
        $this->line('');
        $this->line($url);
        $this->line('');
        $this->warn('请妥善保管该链接，任何持有者都可登录后台。');

        return self::SUCCESS;
    }
}
