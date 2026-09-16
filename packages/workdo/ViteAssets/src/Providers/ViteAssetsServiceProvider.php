<?php

namespace Workdo\ViteAssets\Providers;

use Illuminate\Support\ServiceProvider;

class ViteAssetsServiceProvider extends ServiceProvider
{
 /**
 * Register services.
 */
 public function register(): void
 {
 //
 }
 
 /**
 * Bootstrap services.
 */
 public function boot(): void
 {
 // Load routes
 $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
 }
}