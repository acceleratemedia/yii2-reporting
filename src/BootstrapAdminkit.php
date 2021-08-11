<?php

namespace bvb\reporting;

use yii\base\Event;

class BootstrapAdminkit implements \yii\base\BootstrapInterface
{
    /**
     * Register events and do any other work to bootstrap the membership module
     * @return void
     */
    public function bootstrap($app)
    {
        // --- Add navigation items to the side navigation
        Event::on(
            \bvb\adminkit\widgets\SideNav::class,
            \bvb\adminkit\widgets\SideNav::EVENT_INIT,
            function($event){
                if(!isset($event->sender->items['admin'])){
                    $event->sender->items['admin'] = [
                        'label' => 'Admin',
                        'fontAwesomeIconClass' => 'fas fa-cog',
                        'position' => 1000
                    ];
                }
                $event->sender->items['admin']['items']['reports'] = [
                    'label' => 'Reports',
                    'url' => ['/report/manage/index'],
                    'position' => 100
                ];
            }
        );
    }
}