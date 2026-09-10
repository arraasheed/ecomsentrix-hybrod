<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$loaded = $app->getLoadedProviders();
echo "Is PwaManifestServiceProvider loaded? " . (isset($loaded['Workdo\\PwaManifest\\Providers\\PwaManifestServiceProvider']) ? 'YES' : 'NO') . "\n";
