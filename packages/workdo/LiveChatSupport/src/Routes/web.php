<?php

use Illuminate\Support\Facades\Route;
use Workdo\LiveChatSupport\Http\Controllers\AdminChatInboxController;
use Workdo\LiveChatSupport\Http\Controllers\StorefrontChatController;

Route::middleware('web')->group(function () {
    Route::post('/chat/init', [StorefrontChatController::class, 'init'])
        ->name('livechatsupport.chat.init');
    Route::post('/chat/send', [StorefrontChatController::class, 'send'])
        ->name('livechatsupport.chat.send');
});

Route::middleware(['web', 'auth', 'setlocate'])->name('livechat.')->group(function () {
    Route::get('/admin/live-chat', [AdminChatInboxController::class, 'index'])
        ->name('admin.index');
    Route::post('/admin/live-chat/{conversation}/reply', [AdminChatInboxController::class, 'reply'])
        ->name('admin.reply');
});
