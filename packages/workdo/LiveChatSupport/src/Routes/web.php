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

Route::middleware(['web', 'auth', 'setlocate'])->prefix('chat')->name('livechatsupport.')->group(function () {
    Route::get('/inbox', [AdminChatInboxController::class, 'index'])->name('inbox');
    Route::post('/inbox/{conversation}/reply', [AdminChatInboxController::class, 'reply'])
        ->name('inbox.reply');
});
