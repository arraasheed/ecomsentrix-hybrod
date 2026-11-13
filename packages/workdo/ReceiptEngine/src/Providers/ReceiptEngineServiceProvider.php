<?php

namespace Workdo\ReceiptEngine\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ReceiptEngineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/receiptengine.php', 'receiptengine');
    }

    public function boot(): void
    {
        if (!config('receiptengine.enabled', env('RECEIPT_ENGINE_ENABLED', true))) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'receiptengine');

        View::composer('order.view', function ($view): void {
            $this->pushStack($view, 'invoice-button', 'receiptengine::partials.admin-print-button');
        });

        View::composer('front_end.pages.order-detail', function ($view): void {
            $this->pushStack($view, 'page-script', 'receiptengine::partials.customer-print-button');
        });
    }

    private function pushStack($view, string $stack, string $partial): void
    {
        $factory = $view->getFactory();
        $factory->startPush($stack);
        echo view($partial)->render();
        $factory->stopPush();
    }
}
