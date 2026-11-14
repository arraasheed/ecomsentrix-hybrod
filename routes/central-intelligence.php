<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CentralIntelligenceController;

Route::middleware(['web', 'auth', 'super_admin'])->prefix('central-intelligence')->name('central.')->group(function () {
    // Dashboard
    Route::get('/', [CentralIntelligenceController::class, 'index'])->name('dashboard');

    // Fraud Checks
    Route::get('fraud-checks', [CentralIntelligenceController::class, 'fraudChecks'])->name('fraud.checks');
    Route::post('fraud-checks/search', [CentralIntelligenceController::class, 'searchFraud'])->name('fraud.search');

    // Pixel Blocks
    Route::get('pixel-blocks', [CentralIntelligenceController::class, 'pixelBlocks'])->name('pixel.blocks');
    Route::post('pixel-blocks/search', [CentralIntelligenceController::class, 'searchPixel'])->name('pixel.search');

    // AJAX Lookup
    Route::post('lookup', [CentralIntelligenceController::class, 'lookup'])->name('lookup');
});