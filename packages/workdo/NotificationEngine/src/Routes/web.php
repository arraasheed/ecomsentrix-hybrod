<?php

use Illuminate\Support\Facades\Route;
use Workdo\NotificationEngine\Http\Controllers\NotificationController;
use Workdo\NotificationEngine\Http\Controllers\SuperAdminNotificationController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notificationengine.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])
        ->name('notificationengine.read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])
        ->name('notificationengine.destroy');
    Route::post('/notifications/clear', [NotificationController::class, 'clear'])
        ->name('notificationengine.clear');
});

Route::middleware(['web', 'auth:customers'])->group(function () {
    Route::post('/customer/notifications/{notification}/read', [NotificationController::class, 'read'])
        ->name('notificationengine.customer.read');
});

Route::middleware(['web', 'auth'])->prefix('system-notifications')->name('notificationengine.admin.')->group(function () {
    Route::get('/', [SuperAdminNotificationController::class, 'index'])->name('index');
    Route::post('/', [SuperAdminNotificationController::class, 'store'])->name('store');
});
