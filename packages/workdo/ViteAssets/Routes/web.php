<?php

use Illuminate\Support\Facades\Route;
use Workdo\ViteAssets\Http\Controllers\AssetController;

// Route to serve Vite build assets with compression
Route::get('/build/assets/{file}', [AssetController::class, 'serveAsset'])
 ->where('file', '.*\.(css|js|map)$')
 ->name('vite-assets.serve');

// Apply compression middleware to these routes
Route::group(['middleware' => ['web', \App\Http\Middleware\GzipMiddleware::class]], function () {
 Route::get('/build/assets/{file}', [AssetController::class, 'serveAsset'])
 ->where('file', '.*\.(css|js|map)$')
 ->name('vite-assets.serve-compressed');
});