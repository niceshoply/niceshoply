<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 执行迁移：创建 visits / visit_events / visit_daily / conversion_daily 四张表。
     */
    public function up(): void
    {
        // 访问记录表（会话级聚合统计）
        if (! Schema::hasTable('visits')) {
            Schema::create('visits', function (Blueprint $table) {
                $table->comment('访问记录 - 会话级聚合统计');

                $table->bigIncrements('id')->comment('主键');
                $table->string('session_id', 64)->unique('v_session_id_unique')->comment('会话 ID（每会话唯一）');

                $table->unsignedInteger('customer_id')->nullable()->index('v_customer_id')->comment('客户 ID（登录用户）');
                $table->string('ip_address', 45)->index('v_ip_address')->comment('IP 地址（用于 UV 计算）');

                $table->string('country_code', 2)->nullable()->index('v_country_code')->comment('国家代码（ISO 3166-1 alpha-2）');
                $table->string('country_name', 100)->nullable()->comment('国家名称');
                $table->string('city', 100)->nullable()->comment('城市名称');

                $table->string('device_type', 20)->nullable()->index('v_device_type')->comment('设备类型：desktop/mobile/tablet');
                $table->string('browser', 50)->nullable()->comment('浏览器名称');
                $table->string('os', 50)->nullable()->comment('操作系统');
                $table->text('user_agent')->nullable()->comment('User Agent 字符串');

                $table->string('referrer', 1000)->nullable()->comment('来源 URL（首次访问）');
                $table->string('locale', 10)->nullable()->index('v_locale')->comment('语言代码');
                $table->timestamp('first_visited_at')->index('v_first_visited_at')->comment('首次访问时间');
                $table->timestamp('last_visited_at')->index('v_last_visited_at')->comment('最近访问时间');

                $table->timestamps();

                $table->index(['first_visited_at', 'last_visited_at'], 'v_visit_time_range');
                $table->index(['country_code', 'first_visited_at'], 'v_country_time');
                $table->index(['device_type', 'first_visited_at'], 'v_device_time');
                $table->index(['customer_id', 'first_visited_at'], 'v_customer_time');
                $table->index(['ip_address', 'first_visited_at'], 'v_ip_time');
            });
        }

        // 访问事件表（转化漏斗事件追踪）
        if (! Schema::hasTable('visit_events')) {
            Schema::create('visit_events', function (Blueprint $table) {
                $table->comment('访问事件 - 转化漏斗事件追踪');

                $table->bigIncrements('id')->comment('主键');
                $table->string('session_id', 64)->index('ve_session_id')->comment('会话 ID（通过 session_id 关联 visits）');

                $table->string('event_type', 50)->index('ve_event_type')->comment('事件类型：product_view/add_to_cart/checkout_start/order_placed/payment_completed/register 等');
                $table->json('event_data')->nullable()->comment('事件数据（JSON）');

                $table->unsignedInteger('customer_id')->nullable()->index('ve_customer_id')->comment('客户 ID（登录用户）');
                $table->string('ip_address', 45)->index('ve_ip_address')->comment('IP 地址');

                $table->string('page_url', 1000)->nullable()->comment('事件发生页面 URL');
                $table->string('referrer', 1000)->nullable()->comment('来源 URL');

                $table->timestamps();

                $table->index(['session_id', 'event_type'], 've_session_event');
                $table->index(['event_type', 'created_at'], 've_event_time');
                $table->index(['customer_id', 'created_at'], 've_customer_time');
                $table->index(['created_at'], 've_created_at');
            });
        }

        // 每日访问统计表
        if (! Schema::hasTable('visit_daily')) {
            Schema::create('visit_daily', function (Blueprint $table) {
                $table->comment('每日访问统计 - 由 visits 与 visit_events 聚合');
                $table->date('date')->primary()->comment('统计日期');
                $table->unsignedInteger('pv')->default(0)->comment('页面浏览量 - page_view 事件');
                $table->unsignedInteger('uv')->default(0)->comment('独立访客 - 去重 session_id');
                $table->unsignedInteger('ip')->default(0)->comment('独立 IP - 去重 ip_address');
                $table->unsignedInteger('new_visitors')->default(0)->comment('新访客（无历史访问）');
                $table->unsignedInteger('bounces')->default(0)->comment('跳出 - 仅一次页面浏览的会话');
                $table->unsignedInteger('avg_duration')->default(0)->comment('平均会话时长（秒）');

                $table->unsignedInteger('desktop_pv')->default(0)->comment('桌面端 PV');
                $table->unsignedInteger('mobile_pv')->default(0)->comment('移动端 PV');
                $table->unsignedInteger('tablet_pv')->default(0)->comment('平板端 PV');

                $table->timestamps();
            });
        }

        // 每日转化统计表（含扩展事件列）
        if (! Schema::hasTable('conversion_daily')) {
            Schema::create('conversion_daily', function (Blueprint $table) {
                $table->comment('每日转化统计 - 转化漏斗指标');
                $table->date('date')->primary()->comment('统计日期');

                // 漏斗各阶段计数
                $table->unsignedInteger('home_views')->default(0)->comment('首页浏览');
                $table->unsignedInteger('category_views')->default(0)->comment('分类浏览');
                $table->unsignedInteger('product_views')->default(0)->comment('商品浏览');
                $table->unsignedInteger('add_to_carts')->default(0)->comment('加入购物车事件');
                $table->unsignedInteger('checkout_starts')->default(0)->comment('开始结账事件');
                $table->unsignedInteger('order_placed')->default(0)->comment('已下单');
                $table->unsignedInteger('payment_completed')->default(0)->comment('已支付');
                $table->unsignedInteger('registers')->default(0)->comment('注册');
                $table->unsignedInteger('searches')->default(0)->comment('搜索');
                $table->unsignedInteger('cart_views')->default(0)->comment('购物车浏览');
                $table->unsignedInteger('order_cancelled')->default(0)->comment('订单取消');

                // 转化率（百分比 x100 提升精度，如 25.5% = 2550）
                $table->unsignedInteger('cart_to_checkout_rate')->default(0)->comment('加购 → 结账 %（x100）');
                $table->unsignedInteger('checkout_to_order_rate')->default(0)->comment('结账 → 下单 %（x100）');
                $table->unsignedInteger('order_to_payment_rate')->default(0)->comment('下单 → 支付 %（x100）');
                $table->unsignedInteger('overall_conversion_rate')->default(0)->comment('浏览 → 支付 %（x100）');

                $table->timestamps();
            });
        }
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        Schema::dropIfExists('conversion_daily');
        Schema::dropIfExists('visit_daily');
        Schema::dropIfExists('visit_events');
        Schema::dropIfExists('visits');
    }
};
