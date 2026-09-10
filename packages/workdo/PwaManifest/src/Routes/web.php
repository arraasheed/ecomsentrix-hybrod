<?php

use Illuminate\Support\Facades\Route;
use Workdo\PwaManifest\Http\Controllers\ManifestController;

Route::get('/{slug}/manifest.json', [ManifestController::class, 'show'])->name('pwa.manifest');
