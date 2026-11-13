<?php

namespace Workdo\NotificationEngine\Providers;

use App\Events\SuperAdminMenuEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Workdo\NotificationEngine\Listeners\SuperAdminMenuListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SuperAdminMenuEvent::class => [
            SuperAdminMenuListener::class,
        ],
    ];
}
