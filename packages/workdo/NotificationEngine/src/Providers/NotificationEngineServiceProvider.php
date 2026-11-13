<?php

namespace Workdo\NotificationEngine\Providers;

use App\Models\Order;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Workdo\NotificationEngine\Models\SystemNotification;
use Workdo\NotificationEngine\Services\NotificationService;

class NotificationEngineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/notificationengine.php', 'notificationengine');
        $this->app->singleton(NotificationService::class);
        $this->app->register(EventServiceProvider::class);
    }

    public function boot(): void
    {
        if (!config('notificationengine.enabled', env('NOTIFICATION_ENGINE_ENABLED', true))) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'notificationengine');

        Order::created(function (Order $order): void {
            app(NotificationService::class)->notifyOrderMilestone($order);
        });

        Order::updated(function (Order $order): void {
            if ($order->wasChanged('delivered_status')) {
                app(NotificationService::class)->notifyOrderMilestone($order);
            }
        });

        View::composer('layouts.app', function ($view): void {
            $this->pushStack($view, 'scripts', 'notificationengine::partials.admin-bell');
        });

        View::composer('front_end.pages.account', function ($view): void {
            if (!auth('customers')->check()) {
                return;
            }

            $notifications = SystemNotification::forCustomer((int) auth('customers')->id())
                ->latest()
                ->limit(20)
                ->get();

            $this->pushStack($view, 'affiliatesettings', 'notificationengine::partials.customer-notifications', [
                'notifications' => $notifications,
            ]);
        });
    }

    private function pushStack($view, string $stack, string $partial, array $data = []): void
    {
        $factory = $view->getFactory();
        $factory->startPush($stack);
        echo view($partial, $data)->render();
        $factory->stopPush();
    }
}
