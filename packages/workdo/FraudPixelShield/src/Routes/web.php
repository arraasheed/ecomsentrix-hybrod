<?php

use Illuminate\Support\Facades\Route;
use Workdo\FraudPixelShield\Http\Controllers\FraudShieldController;
use Workdo\FraudPixelShield\Http\Controllers\MetaPixelIsolationController;

// 1. Public Live Checkout Routes (called via AJAX by checkout form)
Route::middleware(['web'])->group(function () {
    Route::post('{slug}/fraud-check', [FraudShieldController::class, 'checkLiveCheckout'])->name('fraud.live.check');
    Route::post('{slug}/fraud-log-lead', [FraudShieldController::class, 'logBlockedLead'])->name('fraud.log.lead');
});

// 2. Store Admin Panel Routes - Fraud Shield
Route::middleware(['web', 'auth', 'setlocate'])->group(function () {
    Route::prefix('fraud-shield')->name('fraud.shield.')->group(function () {
        Route::get('manual-checker', [FraudShieldController::class, 'manualChecker'])->name('manual.checker');
        Route::post('query', [FraudShieldController::class, 'manualNumberQuery'])->name('query');
        Route::get('leads', [FraudShieldController::class, 'leads'])->name('leads');
    });

    // 3. Store Admin Panel Routes - Meta Pixel Isolation & Delayed CAPI
    Route::prefix('meta-pixel')->name('fraud.pixel.')->group(function () {
        Route::get('dashboard', [MetaPixelIsolationController::class, 'dashboard'])->name('dashboard');
        Route::post('toggle', [MetaPixelIsolationController::class, 'toggleStatus'])->name('toggle');
        Route::get('settings', [MetaPixelIsolationController::class, 'dashboard'])->name('settings');
        Route::post('settings', [MetaPixelIsolationController::class, 'updateSettings'])->name('settings.update');
        Route::post('capi/trigger/{order}', [MetaPixelIsolationController::class, 'triggerCapi'])->name('capi.trigger');
        Route::get('capi-logs', [MetaPixelIsolationController::class, 'capiLogs'])->name('capi.logs');
        Route::post('capi/retry/{log}', [MetaPixelIsolationController::class, 'retryCapi'])->name('capi.retry');
    });
});
