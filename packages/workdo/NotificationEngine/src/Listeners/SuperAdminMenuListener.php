<?php

namespace Workdo\NotificationEngine\Listeners;

use App\Events\SuperAdminMenuEvent;

class SuperAdminMenuListener
{
    public function handle(SuperAdminMenuEvent $event): void
    {
        $event->menu->add([
            'title' => __('System Notifications'),
            'icon' => 'bell',
            'name' => 'system-notifications',
            'parent' => null,
            'order' => 275,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'notificationengine.admin.index',
            'module' => 'NotificationEngine',
            'permission' => '',
        ]);
    }
}
