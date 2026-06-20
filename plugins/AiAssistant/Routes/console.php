<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\AiAssistant\Controllers\Console\AiAssistantController;

Route::get('/ai-assistant/kb', [AiAssistantController::class, 'kb'])->name('ai_assistant.kb');
Route::post('/ai-assistant/kb', [AiAssistantController::class, 'storeKb'])->name('ai_assistant.kb.store');
Route::delete('/ai-assistant/kb/{id}', [AiAssistantController::class, 'destroyKb'])->name('ai_assistant.kb.destroy');
Route::get('/ai-assistant/conversations', [AiAssistantController::class, 'conversations'])->name('ai_assistant.conversations');
