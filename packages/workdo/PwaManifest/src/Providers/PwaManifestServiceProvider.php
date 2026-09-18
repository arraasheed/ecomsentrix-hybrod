<?php

namespace Workdo\PwaManifest\Providers;

use Illuminate\Support\ServiceProvider;

class PwaManifestServiceProvider extends ServiceProvider
{
    protected $moduleName = 'PwaManifest';
    protected $moduleNameLower = 'pwamanifest';

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
