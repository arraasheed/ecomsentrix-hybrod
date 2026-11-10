<?php

namespace Workdo\FraudPixelShield\Providers;

use Illuminate\Support\ServiceProvider;

class FraudPixelShieldServiceProvider extends ServiceProvider
{
    protected $moduleName = 'FraudPixelShield';
    protected $moduleNameLower = 'fraudpixelshield';

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'fraud-pixel-shield');
    }

    public function register()
    {
        // Bind any services or singletons if needed
    }
}
