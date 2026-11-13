<?php

namespace Workdo\LiveChatSupport\Providers;

use Illuminate\Support\ServiceProvider;

class LiveChatSupportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/chat.php', 'chat');
        $this->app->register(EventServiceProvider::class);
    }

    public function boot(): void
    {
        if (!config('chat.enabled', env('LIVE_CHAT_ENABLED', true))) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'livechatsupport');
    }
}
