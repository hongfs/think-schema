<?php

namespace Hongfs\ThinkSchema;

use think\Service as BaseService;

class Service extends BaseService
{
    public function register()
    {
        // 注册命令
        $this->commands([
            'schema:load' => command\SchemaLoad::class,
        ]);

        // 注册事件
        $this->app->event->bind([
            'HttpRun' => [
                'Hongfs\ThinkSchema\event\SchemaLoad',
            ],
        ]);
    }
}
