<?php
use Illuminate\Support\Facades\Route;
use Plugin\GlobalIm\Controllers\WebhookController;

Route::post('/webhook/global-im/telegram', [WebhookController::class, 'telegram'])->name('global_im.webhook.telegram');
Route::match(['get', 'post'], '/webhook/global-im/whatsapp', [WebhookController::class, 'whatsapp'])->name('global_im.webhook.whatsapp');
