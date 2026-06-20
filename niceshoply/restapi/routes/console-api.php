<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use NiceShoply\RestAPI\ConsoleApiControllers;

Route::get('/', [ConsoleApiControllers\IntroductionController::class, 'index'])->name('base.index');
Route::post('/login', [ConsoleApiControllers\AuthController::class, 'login'])->middleware('throttle:10,1')->name('auth.login');
Route::post('/refresh', [ConsoleApiControllers\AuthController::class, 'refresh'])->name('auth.refresh');

Route::middleware(['jwt.auth'])->group(function () {

    Route::post('/logout', [ConsoleApiControllers\AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/admin', [ConsoleApiControllers\AuthController::class, 'admin'])->name('auth.admin');

    Route::get('/dashboard', [ConsoleApiControllers\DashboardController::class, 'index'])->name('dashboard.index');

    Route::get('/products', [ConsoleApiControllers\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/names', [ConsoleApiControllers\ProductController::class, 'names'])->name('products.names');
    Route::get('/products/autocomplete', [ConsoleApiControllers\ProductController::class, 'autocomplete'])->name('products.autocomplete');
    Route::get('/products/sku_autocomplete', [ConsoleApiControllers\ProductController::class, 'skuAutocomplete'])->name('products.sku_autocomplete');
    Route::post('/products/import', [ConsoleApiControllers\ProductController::class, 'import'])->name('products.import');
    Route::put('/products/{spu_code}', [ConsoleApiControllers\ProductController::class, 'update'])->name('products.update');
    Route::patch('/products/{spu_code}', [ConsoleApiControllers\ProductController::class, 'patch'])->name('products.patch');

    Route::get('/categories', [ConsoleApiControllers\CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/names', [ConsoleApiControllers\CategoryController::class, 'names'])->name('categories.names');
    Route::get('/categories/autocomplete', [ConsoleApiControllers\CategoryController::class, 'autocomplete'])->name('categories.autocomplete');

    Route::get('/brands', [ConsoleApiControllers\BrandController::class, 'index'])->name('brands.index');
    Route::get('/brands/names', [ConsoleApiControllers\BrandController::class, 'names'])->name('brands.name');
    Route::get('/brands/autocomplete', [ConsoleApiControllers\BrandController::class, 'autocomplete'])->name('brands.autocomplete');

    Route::get('/articles', [ConsoleApiControllers\ArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/names', [ConsoleApiControllers\ArticleController::class, 'names'])->name('articles.names');
    Route::get('/articles/autocomplete', [ConsoleApiControllers\ArticleController::class, 'autocomplete'])->name('articles.autocomplete');
    Route::post('/articles', [ConsoleApiControllers\ArticleController::class, 'store'])->name('articles.store');
    Route::put('/articles/{article}', [ConsoleApiControllers\ArticleController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article}', [ConsoleApiControllers\ArticleController::class, 'destroy'])->name('articles.destroy');

    Route::get('/catalogs', [ConsoleApiControllers\CatalogController::class, 'index'])->name('catalogs.index');
    Route::get('/catalogs/names', [ConsoleApiControllers\CatalogController::class, 'names'])->name('catalogs.names');
    Route::get('/catalogs/autocomplete', [ConsoleApiControllers\CatalogController::class, 'autocomplete'])->name('catalogs.autocomplete');
    Route::post('/catalogs', [ConsoleApiControllers\CatalogController::class, 'store'])->name('catalogs.store');
    Route::put('/catalogs/{catalog}', [ConsoleApiControllers\CatalogController::class, 'update'])->name('catalogs.update');
    Route::delete('/catalogs/{catalog}', [ConsoleApiControllers\CatalogController::class, 'destroy'])->name('catalogs.destroy');

    Route::post('/orders/{order}/notes', [ConsoleApiControllers\OrderController::class, 'updateNote'])->name('orders.update_note');

    Route::post('/orders/{order}/shipments', [ConsoleApiControllers\ShipmentController::class, 'store'])->name('shipments.store');
    Route::delete('/shipments/{shipment}', [ConsoleApiControllers\ShipmentController::class, 'destroy'])->name('shipments.destroy');
    Route::get('/shipments/{shipment}/traces', [ConsoleApiControllers\ShipmentController::class, 'getTraces'])->name('shipments.get_traces');

    Route::get('/pages', [ConsoleApiControllers\PageController::class, 'index'])->name('pages.index');
    Route::get('/pages/names', [ConsoleApiControllers\PageController::class, 'names'])->name('pages.names');
    Route::get('/pages/autocomplete', [ConsoleApiControllers\PageController::class, 'autocomplete'])->name('pages.autocomplete');
    Route::post('/pages', [ConsoleApiControllers\PageController::class, 'store'])->name('pages.store');
    Route::put('/pages/{page}', [ConsoleApiControllers\PageController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{page}', [ConsoleApiControllers\PageController::class, 'destroy'])->name('pages.destroy');

    Route::get('/tags', [ConsoleApiControllers\TagController::class, 'index'])->name('tags.index');
    Route::get('/tags/names', [ConsoleApiControllers\TagController::class, 'names'])->name('tags.name');
    Route::get('/tags/autocomplete', [ConsoleApiControllers\TagController::class, 'autocomplete'])->name('tags.autocomplete');
    Route::post('/tags', [ConsoleApiControllers\TagController::class, 'store'])->name('tags.store');
    Route::put('/tags/{tag}', [ConsoleApiControllers\TagController::class, 'update'])->name('tags.update');
    Route::delete('/tags/{tag}', [ConsoleApiControllers\TagController::class, 'destroy'])->name('tags.destroy');

    Route::get('/attributes', [ConsoleApiControllers\AttributeController::class, 'index'])->name('attributes.index');
    Route::get('/attribute_values', [ConsoleApiControllers\AttributeValueController::class, 'index'])->name('attribute_values.index');

    Route::get('/options/available', [ConsoleApiControllers\OptionController::class, 'available'])->name('options.available');
    Route::get('/options/{option}/values', [ConsoleApiControllers\OptionController::class, 'values'])->name('options.values');

    Route::get('/customers', [ConsoleApiControllers\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/names', [ConsoleApiControllers\CustomerController::class, 'names'])->name('customers.name');
    Route::get('/customers/autocomplete', [ConsoleApiControllers\CustomerController::class, 'autocomplete'])->name('customers.autocomplete');
    Route::post('/customers', [ConsoleApiControllers\CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{tag}', [ConsoleApiControllers\CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{tag}', [ConsoleApiControllers\CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('/file_manager/files', [ConsoleApiControllers\FileManagerController::class, 'getFiles'])->name('file_manager.get_files');
    Route::get('/file_manager/directories', [ConsoleApiControllers\FileManagerController::class, 'getDirectories'])->name('file_manager.get_directories');
    Route::get('/file_manager/directories_lazy', [ConsoleApiControllers\FileManagerController::class, 'getDirectoriesLazy'])->name('file_manager.get_directories_lazy');
    Route::post('/file_manager/directories', [ConsoleApiControllers\FileManagerController::class, 'createDirectory'])->name('file_manager.create_directory');
    Route::post('/file_manager/upload', [ConsoleApiControllers\FileManagerController::class, 'uploadFiles'])->name('file_manager.upload');
    Route::post('/file_manager/rename', [ConsoleApiControllers\FileManagerController::class, 'rename'])->name('file_manager.rename');
    Route::delete('/file_manager/files', [ConsoleApiControllers\FileManagerController::class, 'destroyFiles'])->name('file_manager.delete_files');
    Route::delete('/file_manager/directories', [ConsoleApiControllers\FileManagerController::class, 'destroyDirectories'])->name('file_manager.delete_directories');
    Route::post('/file_manager/move_directories', [ConsoleApiControllers\FileManagerController::class, 'moveDirectories'])->name('file_manager.move_directories');
    Route::post('/file_manager/move_files', [ConsoleApiControllers\FileManagerController::class, 'moveFiles'])->name('file_manager.move_files');
    Route::post('/file_manager/copy_files', [ConsoleApiControllers\FileManagerController::class, 'copyFiles'])->name('file_manager.copy_files');

    // Warehouses
    Route::get('/warehouses', [ConsoleApiControllers\WarehouseController::class, 'index'])->name('warehouses.index');
    Route::get('/warehouses/active', [ConsoleApiControllers\WarehouseController::class, 'active'])->name('warehouses.active');
    Route::post('/warehouses', [ConsoleApiControllers\WarehouseController::class, 'store'])->name('warehouses.store');
    Route::get('/warehouses/{warehouse}', [ConsoleApiControllers\WarehouseController::class, 'show'])->name('warehouses.show');
    Route::put('/warehouses/{warehouse}', [ConsoleApiControllers\WarehouseController::class, 'update'])->name('warehouses.update');
    Route::delete('/warehouses/{warehouse}', [ConsoleApiControllers\WarehouseController::class, 'destroy'])->name('warehouses.destroy');

    // Warehouse Stocks
    Route::get('/warehouse_stocks', [ConsoleApiControllers\WarehouseStockController::class, 'index'])->name('warehouse_stocks.index');
    Route::post('/warehouse_stocks/adjust', [ConsoleApiControllers\WarehouseStockController::class, 'adjust'])->name('warehouse_stocks.adjust');
    Route::get('/warehouse_stock_movements', [ConsoleApiControllers\WarehouseStockController::class, 'movements'])->name('warehouse_stock_movements.index');

    // Announcements 顶部公告
    Route::get('/announcements', [ConsoleApiControllers\AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [ConsoleApiControllers\AnnouncementController::class, 'store'])->name('announcements.store');
    Route::put('/announcements/{announcement}', [ConsoleApiControllers\AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('/announcements/{announcement}', [ConsoleApiControllers\AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    // AI 图片生成
    Route::get('/ai/image/models', [ConsoleApiControllers\AIImageController::class, 'modelsInfo'])->name('ai.image.models');
    Route::post('/ai/image/generate', [ConsoleApiControllers\AIImageController::class, 'generate'])->name('ai.image.generate');

    // Newsletter 订阅者管理
    Route::get('/newsletters', [ConsoleApiControllers\NewsletterController::class, 'index'])->name('newsletters.index');
    Route::get('/newsletters/statistics', [ConsoleApiControllers\NewsletterController::class, 'statistics'])->name('newsletters.statistics');
    Route::get('/newsletters/export', [ConsoleApiControllers\NewsletterController::class, 'export'])->name('newsletters.export');
    Route::post('/newsletters', [ConsoleApiControllers\NewsletterController::class, 'store'])->name('newsletters.store');
    Route::put('/newsletters/{newsletter}', [ConsoleApiControllers\NewsletterController::class, 'update'])->name('newsletters.update');
    Route::delete('/newsletters/{newsletter}', [ConsoleApiControllers\NewsletterController::class, 'destroy'])->name('newsletters.destroy');

    // Visits 访问追踪与分析
    Route::get('/visits', [ConsoleApiControllers\VisitController::class, 'index'])->name('visits.index');
    Route::get('/visits/statistics', [ConsoleApiControllers\VisitController::class, 'statistics'])->name('visits.statistics');
    Route::post('/visits/aggregate', [ConsoleApiControllers\VisitController::class, 'aggregate'])->name('visits.aggregate');
    Route::post('/visits/enrich', [ConsoleApiControllers\VisitController::class, 'enrich'])->name('visits.enrich');

    // Plugin Coordination 插件编排
    Route::get('/plugin_coordinations', [ConsoleApiControllers\PluginCoordinationController::class, 'index'])->name('plugin_coordinations.index');
    Route::put('/plugin_coordinations', [ConsoleApiControllers\PluginCoordinationController::class, 'update'])->name('plugin_coordinations.update');

    // Stock Transfers
    Route::get('/stock_transfers', [ConsoleApiControllers\StockTransferController::class, 'index'])->name('stock_transfers.index');
    Route::post('/stock_transfers', [ConsoleApiControllers\StockTransferController::class, 'store'])->name('stock_transfers.store');
    Route::get('/stock_transfers/{stock_transfer}', [ConsoleApiControllers\StockTransferController::class, 'show'])->name('stock_transfers.show');
    Route::put('/stock_transfers/{stock_transfer}/ship', [ConsoleApiControllers\StockTransferController::class, 'ship'])->name('stock_transfers.ship');
    Route::put('/stock_transfers/{stock_transfer}/complete', [ConsoleApiControllers\StockTransferController::class, 'complete'])->name('stock_transfers.complete');
    Route::put('/stock_transfers/{stock_transfer}/cancel', [ConsoleApiControllers\StockTransferController::class, 'cancel'])->name('stock_transfers.cancel');
});
