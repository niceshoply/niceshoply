<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use NiceShoply\Console\Controllers;

Route::get('login', [Controllers\LoginController::class, 'index'])->name('login.index');
Route::post('login', [Controllers\LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');

// CLI 一次性登录：由 `php artisan admin:cli-login` 生成带签名的临时链接，
// 运维在浏览器打开后免密登录后台。`signed` 中间件负责校验签名与有效期。
Route::get('cli-login/{admin}', [Controllers\CliLoginController::class, 'login'])
    ->middleware('signed')
    ->name('cli_login');

Route::middleware(['admin_auth:admin'])
    ->group(function () {
        Route::get('logout', [Controllers\LogoutController::class, 'index'])->name('logout.index');

        Route::get('/', [Controllers\DashboardController::class, 'index'])->name('dashboard.index');

        Route::get('/locale/{code}', [Controllers\LocaleController::class, 'switch'])->name('locale.switch');

        Route::get('/menu/search', [Controllers\MenuSearchController::class, 'search'])->name('menu.search');

        Route::post('/upload/images', [Controllers\UploadController::class, 'images'])->name('upload.images');
        Route::post('/upload/files', [Controllers\UploadController::class, 'files'])->name('upload.files');

        Route::post('/translations/translate-text', [Controllers\TranslationController::class, 'translateText'])->name('translations.trans_text');
        Route::post('/translations/translate-html', [Controllers\TranslationController::class, 'translateHtml'])->name('translations.trans_html');

        Route::get('/orders/export', [Controllers\OrderController::class, 'exportBatch'])->name('orders.export.batch');
        Route::get('/orders/export/download', [Controllers\OrderController::class, 'downloadExport'])->name('orders.export.download');
        Route::resource('/orders', Controllers\OrderController::class);
        Route::get('/orders/{order}/printing', [Controllers\OrderController::class, 'printing'])->name('orders.printing');
        Route::put('/orders/{order}/status', [Controllers\OrderController::class, 'changeStatus'])->name('orders.change_status');

        Route::get('/order_returns/export', [Controllers\OrderReturnController::class, 'export'])->name('order_returns.export');
        Route::put('/order_returns/bulk/status', [Controllers\OrderReturnController::class, 'bulkStatus'])->name('order_returns.bulk.status');
        Route::resource('/order_returns', Controllers\OrderReturnController::class);
        Route::put('/order_returns/{order_return}/status', [Controllers\OrderReturnController::class, 'changeStatus'])->name('order_returns.change_status');
        Route::post('/order_returns/{order_return}/refund', [Controllers\OrderReturnController::class, 'refund'])->name('order_returns.refund');

        // Refunds：退款单闭环（状态机 + 原路/余额/人工）
        Route::get('/refunds', [Controllers\RefundController::class, 'index'])->name('refunds.index');
        Route::post('/refunds', [Controllers\RefundController::class, 'store'])->name('refunds.store');
        Route::get('/refunds/{refund}', [Controllers\RefundController::class, 'show'])->name('refunds.show');
        Route::post('/refunds/{refund}/process', [Controllers\RefundController::class, 'process'])->name('refunds.process');
        Route::post('/refunds/{refund}/cancel', [Controllers\RefundController::class, 'cancel'])->name('refunds.cancel');

        // Return reasons
        Route::resource('/return_reasons', Controllers\ReturnReasonController::class);
        Route::put('/return_reasons/{id}/active', [Controllers\ReturnReasonController::class, 'active'])->name('return_reasons.active');

        Route::put('/products/{product}/active', [Controllers\ProductController::class, 'active'])->name('products.active');
        Route::get('/products/{product}/copy', [Controllers\ProductController::class, 'copy'])->name('products.copy');
        Route::post('/products/bulk/update', [Controllers\ProductController::class, 'bulkUpdate'])->name('products.bulk.update');
        Route::delete('/products/bulk/destroy', [Controllers\ProductController::class, 'bulkDestroy'])->name('products.destroy.batch');
        Route::get('/products/selector', [Controllers\ProductSelectorController::class, 'selectorPage'])->name('products.selector');
        Route::resource('/products', Controllers\ProductController::class);

        Route::put('/payments/{payment}/active', [Controllers\PaymentController::class, 'active'])->name('payments.active');

        Route::resource('/categories', Controllers\CategoryController::class);
        Route::put('/categories/{category}/active', [Controllers\CategoryController::class, 'active'])->name('categories.active');

        Route::resource('/attribute_groups', Controllers\AttributeGroupController::class);

        Route::resource('/attributes', Controllers\AttributeController::class);

        Route::resource('/attribute_values', Controllers\AttributeValueController::class);

        Route::get('/options/available', [Controllers\OptionController::class, 'available'])->name('options.available');
        Route::get('/options/{optionId}/values', [Controllers\OptionController::class, 'valuesByOptionId'])->name('options.values');
        Route::resource('/options', Controllers\OptionController::class);
        Route::put('/options/{option}/active', [Controllers\OptionController::class, 'active'])->name('options.active');

        Route::resource('/option_values', Controllers\OptionValueController::class);
        Route::put('/option_values/{option_value}/active', [Controllers\OptionValueController::class, 'active'])->name('option_values.active');

        Route::resource('/brands', Controllers\BrandController::class);
        Route::put('/brands/{currency}/active', [Controllers\BrandController::class, 'active'])->name('brands.active');

        Route::get('/reviews/stats/summary', [Controllers\ReviewController::class, 'stats'])->name('reviews.stats');
        Route::resource('/reviews', Controllers\ReviewController::class);
        Route::put('/reviews/{review}/active', [Controllers\ReviewController::class, 'active'])->name('reviews.active');
        Route::post('/reviews/{review}/approve', [Controllers\ReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('/reviews/{review}/reject', [Controllers\ReviewController::class, 'reject'])->name('reviews.reject');
        Route::post('/reviews/{review}/reply', [Controllers\ReviewController::class, 'reply'])->name('reviews.reply');

        Route::resource('/articles', Controllers\ArticleController::class);
        Route::put('/articles/{currency}/active', [Controllers\ArticleController::class, 'active'])->name('articles.active');

        Route::resource('/catalogs', Controllers\CatalogController::class);
        Route::put('/catalogs/{catalog}/active', [Controllers\CatalogController::class, 'active'])->name('catalogs.active');

        Route::resource('/tags', Controllers\TagController::class);
        Route::put('/tags/{tag}/active', [Controllers\TagController::class, 'active'])->name('tags.active');

        Route::resource('/pages', Controllers\PageController::class);
        Route::put('/pages/{page}/active', [Controllers\PageController::class, 'active'])->name('pages.active');

        Route::resource('/customers', Controllers\CustomerController::class);
        Route::get('/customers/{customer}/login', [Controllers\CustomerController::class, 'loginFrontend'])->name('customers.login');
        Route::put('/customers/{customer}/active', [Controllers\CustomerController::class, 'active'])->name('customers.active');

        Route::get('/transactions', [Controllers\TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/create', [Controllers\TransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions', [Controllers\TransactionController::class, 'store'])->name('transactions.store');
        Route::get('/transactions/{transaction}', [Controllers\TransactionController::class, 'show'])->name('transactions.show');

        Route::get('/withdrawals', [Controllers\CustomerWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::get('/withdrawals/{withdrawal}', [Controllers\CustomerWithdrawalController::class, 'show'])->name('withdrawals.show');
        Route::put('/withdrawals/{withdrawal}/status', [Controllers\CustomerWithdrawalController::class, 'changeStatus'])->name('withdrawals.change_status');

        Route::resource('/customer_groups', Controllers\CustomerGroupController::class);
        Route::get('/social', [Controllers\SocialController::class, 'index'])->name('socials.index');
        Route::post('/social', [Controllers\SocialController::class, 'store'])->name('socials.store');

        Route::get('/analytics', [Controllers\AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/analytics/order', [Controllers\AnalyticsController::class, 'order'])->name('analytics_order');
        Route::get('/analytics/product', [Controllers\AnalyticsController::class, 'product'])->name('analytics_product');
        Route::get('/analytics/customer', [Controllers\AnalyticsController::class, 'customer'])->name('analytics_customer');

        // Finance：财务对账
        Route::get('/reconciliation', [Controllers\ReconciliationController::class, 'index'])->name('reconciliation.index');
        Route::get('/reconciliation/export', [Controllers\ReconciliationController::class, 'export'])->name('reconciliation.export');

        // 访问追踪与分析（明细 + GeoIP + 转化漏斗）
        Route::get('/visits', [Controllers\VisitController::class, 'index'])->name('visits.index');
        Route::get('/visits/statistics', [Controllers\VisitController::class, 'statistics'])->name('visits.statistics');
        Route::post('/visits/enrich', [Controllers\VisitController::class, 'enrich'])->name('visits.enrich');
        Route::post('/visits/aggregate', [Controllers\VisitController::class, 'aggregate'])->name('visits.aggregate');

        Route::get('/locales', [Controllers\LocaleController::class, 'index'])->name('locales.index');
        Route::post('/locales/install', [Controllers\LocaleController::class, 'install'])->name('locales.install');
        Route::get('/locales/{locale}/edit', [Controllers\LocaleController::class, 'edit'])->name('locales.edit');
        Route::put('/locales/{locale}', [Controllers\LocaleController::class, 'update'])->name('locales.update');
        Route::post('/locales/{code}/uninstall', [Controllers\LocaleController::class, 'uninstall'])->name('locales.uninstall');
        Route::put('/locales/{country}/active', [Controllers\LocaleController::class, 'active'])->name('locales.active');

        Route::get('/themes', [Controllers\ThemeController::class, 'index'])->name('themes.index');
        Route::put('/themes/{code}/active', [Controllers\ThemeController::class, 'enable'])->name('themes.active');
        Route::get('/themes/settings', [Controllers\ThemeController::class, 'settings'])->name('themes_settings.index');
        Route::put('/themes/settings', [Controllers\ThemeController::class, 'updateSettings'])->name('themes_settings.update');
        Route::post('/themes/{code}/import-demo', [Controllers\ThemeController::class, 'importDemo'])->name('themes.import_demo');
        Route::get('/themes/{code}/export-sql', [Controllers\ThemeController::class, 'exportSql'])->name('themes.export_sql');

        Route::get('/account', [Controllers\AccountController::class, 'index'])->name('account.index');
        Route::put('/account', [Controllers\AccountController::class, 'update'])->name('account.update');

        Route::get('/settings', [Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [Controllers\SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-sms', [Controllers\SettingController::class, 'testSms'])->name('settings.test_sms');

        Route::post('/content_ai/generate', [Controllers\ContentAIController::class, 'generate'])->name('content_ai.generate');
        Route::match(['get', 'post'], '/content_ai/stream', [Controllers\ContentAIController::class, 'stream'])->name('content_ai.stream');
        Route::get('/content_ai/status', [Controllers\ContentAIController::class, 'status'])->name('content_ai.status');

        Route::resource('/admins', Controllers\AdminController::class);
        Route::put('/admins/{currency}/active', [Controllers\AdminController::class, 'active'])->name('admins.active');

        Route::resource('/roles', Controllers\RoleController::class);

        Route::resource('/currencies', Controllers\CurrencyController::class);
        Route::put('/currencies/{currency}/active', [Controllers\CurrencyController::class, 'active'])->name('currencies.active');

        Route::resource('/countries', Controllers\CountryController::class);
        Route::put('/countries/{country}/active', [Controllers\CountryController::class, 'active'])->name('countries.active');

        Route::resource('/states', Controllers\StateController::class);
        Route::put('/states/{state}/active', [Controllers\StateController::class, 'active'])->name('states.active');

        Route::resource('/regions', Controllers\RegionController::class);
        Route::put('/regions/{state}/active', [Controllers\RegionController::class, 'active'])->name('regions.active');

        Route::resource('/tax_classes', Controllers\TaxClassController::class);
        Route::resource('/tax_rates', Controllers\TaxRateController::class);

        Route::resource('/weight_classes', Controllers\WeightClassController::class);
        Route::put('/weight_classes/{id}/active', [Controllers\WeightClassController::class, 'active'])->name('weight_classes.active');

        Route::get('/file_manager', [NiceShoply\RestAPI\ConsoleApiControllers\FileManagerController::class, 'index'])->name('file_manager.index');
        Route::get('/file_manager/iframe', [NiceShoply\RestAPI\ConsoleApiControllers\FileManagerController::class, 'iframe'])->name('file_manager.iframe');

        // Warehouse management
        Route::resource('/warehouses', Controllers\WarehouseController::class);
        Route::put('/warehouses/{warehouse}/active', [Controllers\WarehouseController::class, 'active'])->name('warehouses.active');

        // Warehouse stock
        Route::get('/warehouse_stocks', [Controllers\WarehouseStockController::class, 'index'])->name('warehouse_stocks.index');
        Route::match(['get', 'post'], '/warehouse_stocks/export', [Controllers\WarehouseStockController::class, 'export'])->name('warehouse_stocks.export');
        Route::get('/warehouse_stocks/template', [Controllers\WarehouseStockController::class, 'template'])->name('warehouse_stocks.template');
        Route::post('/warehouse_stocks/import', [Controllers\WarehouseStockController::class, 'import'])->name('warehouse_stocks.import');
        Route::post('/warehouse_stocks/adjust', [Controllers\WarehouseStockController::class, 'adjust'])->name('warehouse_stocks.adjust');
        Route::get('/warehouse_stocks/recent_movements', [Controllers\WarehouseStockController::class, 'recentMovements'])->name('warehouse_stocks.recent_movements');
        Route::get('/warehouse_stock_movements', [Controllers\WarehouseStockController::class, 'movements'])->name('warehouse_stock_movements.index');

        // Stock transfers
        Route::resource('/stock_transfers', Controllers\StockTransferController::class)->only(['index', 'create', 'store', 'show']);
        Route::put('/stock_transfers/{stock_transfer}/ship', [Controllers\StockTransferController::class, 'ship'])->name('stock_transfers.ship');
        Route::put('/stock_transfers/{stock_transfer}/complete', [Controllers\StockTransferController::class, 'complete'])->name('stock_transfers.complete');
        Route::put('/stock_transfers/{stock_transfer}/cancel', [Controllers\StockTransferController::class, 'cancel'])->name('stock_transfers.cancel');

        Route::resource('/customer_groups', Controllers\CustomerGroupController::class);

        // 会员等级 + 积分流水
        Route::resource('/member_levels', Controllers\MemberLevelController::class);
        Route::put('/member_levels/{id}/active', [Controllers\MemberLevelController::class, 'active'])->name('member_levels.active');
        Route::get('/point_logs', [Controllers\PointLogController::class, 'index'])->name('point_logs.index');
        Route::post('/point_logs/adjust', [Controllers\PointLogController::class, 'adjust'])->name('point_logs.adjust');

        // Marketing：促销活动 + 优惠券
        Route::resource('/promotions', Controllers\PromotionController::class);
        Route::put('/promotions/{id}/active', [Controllers\PromotionController::class, 'active'])->name('promotions.active');

        Route::get('/coupons/{coupon}/usages', [Controllers\CouponController::class, 'usages'])->name('coupons.usages');
        Route::resource('/coupons', Controllers\CouponController::class);
        Route::put('/coupons/{id}/active', [Controllers\CouponController::class, 'active'])->name('coupons.active');

        // 弃购挽回：召回记录与转化统计
        Route::get('/abandoned_carts', [Controllers\AbandonedCartController::class, 'index'])->name('abandoned_carts.index');

        // SEO：URL 重定向
        Route::resource('/redirects', Controllers\RedirectController::class);

        // 合规：法律文档 + GDPR 申请
        Route::resource('/legal_documents', Controllers\LegalDocumentController::class);
        Route::get('/gdpr_requests', [Controllers\GdprRequestController::class, 'index'])->name('gdpr_requests.index');
        Route::get('/gdpr_requests/{id}/download', [Controllers\GdprRequestController::class, 'download'])->name('gdpr_requests.download');

        // Shipping：配送区域 + 运费模板
        Route::resource('/shipping_zones', Controllers\ShippingZoneController::class);
        Route::put('/shipping_zones/{id}/active', [Controllers\ShippingZoneController::class, 'active'])->name('shipping_zones.active');
        Route::resource('/shipping_templates', Controllers\ShippingTemplateController::class);
        Route::put('/shipping_templates/{id}/active', [Controllers\ShippingTemplateController::class, 'active'])->name('shipping_templates.active');

        // Audit logs
        Route::get('/audit_logs', [Controllers\AuditLogController::class, 'index'])->name('audit_logs.index');

        // System update（在线升级）
        // 注意：progress / log 为 GET，且已在 bootstrap/app.php 维护模式白名单放行，升级期间可持续轮询。
        Route::get('/system_update', [Controllers\SystemUpdateController::class, 'index'])->name('system_update.index');
        Route::post('/system_update/check', [Controllers\SystemUpdateController::class, 'check'])->name('system_update.check');
        Route::post('/system_update/start', [Controllers\SystemUpdateController::class, 'start'])->name('system_update.start');
        Route::get('/system_update/progress', [Controllers\SystemUpdateController::class, 'progress'])->name('system_update.progress');
        Route::get('/system_update/log', [Controllers\SystemUpdateController::class, 'log'])->name('system_update.log');

        // 平台运维：备份 / 健康自检 / 计划任务
        Route::get('/backups', [Controllers\BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [Controllers\BackupController::class, 'store'])->name('backups.store');
        Route::get('/backups/progress', [Controllers\BackupController::class, 'progress'])->name('backups.progress');
        Route::get('/backups/{id}/download', [Controllers\BackupController::class, 'download'])->name('backups.download');
        Route::post('/backups/{id}/restore', [Controllers\BackupController::class, 'restore'])->name('backups.restore');

        Route::get('/health', [Controllers\HealthCheckController::class, 'index'])->name('health.index');

        Route::get('/schedule', [Controllers\ScheduleController::class, 'index'])->name('schedule.index');
        Route::post('/schedule/run', [Controllers\ScheduleController::class, 'run'])->name('schedule.run');
    });
