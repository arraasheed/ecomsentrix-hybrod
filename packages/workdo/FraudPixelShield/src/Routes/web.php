<?php

use Illuminate\Support\Facades\Route;
use Workdo\FraudPixelShield\Http\Controllers\FraudShieldController;

// 1. Public Live Checkout Routes (called via AJAX by checkout form)
Route::middleware(['web'])->group(function () {
    Route::post('{slug}/fraud-check', [FraudShieldController::class, 'checkLiveCheckout'])->name('fraud.live.check');
    Route::post('{slug}/fraud-log-lead', [FraudShieldController::class, 'logBlockedLead'])->name('fraud.log.lead');
});

// 2. Store Admin Panel Routes
Route::middleware(['web', 'auth', 'setlocate'])->group(function () {
    Route::prefix('fraud-shield')->name('fraud.shield.')->group(function () {
        Route::get('manual-checker', [FraudShieldController::class, 'manualChecker'])->name('manual.checker');
        Route::post('query', [FraudShieldController::class, 'manualNumberQuery'])->name('query');
        Route::get('leads', [FraudShieldController::class, 'leads'])->name('leads');
    });
});
