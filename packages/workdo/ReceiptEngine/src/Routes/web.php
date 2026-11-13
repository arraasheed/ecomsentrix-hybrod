<?php

use Illuminate\Support\Facades\Route;
use Workdo\ReceiptEngine\Http\Controllers\ReceiptController;

Route::middleware('web')->get('/receipt-engine/{order}', [ReceiptController::class, 'show'])
    ->name('receiptengine.show');
