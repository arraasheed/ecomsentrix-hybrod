<?php

namespace Workdo\LiveChatSupport\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    public function handle(CompanyMenuEvent $event): void
    {
        $menu = $event->menu;

        $menu->add([
            'title' => __('⚡ Smart Features'),
            'icon' => 'sparkles',
            'name' => 'smart-features',
            'parent' => null,
            'order' => 275,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => 'LiveChatSupport',
            'permission' => 'Manage Live Chat',
        ]);

        $menu->add([
            'title' => __('Live Chat'),
            'icon' => 'messages',
            'name' => 'livechat.admin.index',
            'parent' => 'smart-features',
            'order' => 1,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'livechat.admin.index',
            'module' => 'LiveChatSupport',
            'permission' => 'Manage Live Chat',
        ]);
    }
}
