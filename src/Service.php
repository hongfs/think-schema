<?php

namespace Hongfs\ThinkSchema;

use think\Service as BaseService;

class Service extends BaseService
{
    public function register()
    {
        $this->commands([
            'schema:load' => command\SchemaLoad::class,
        ]);

        $this->app->event->bind([
            'HttpRun' => [
                'Hongfs\ThinkSchema\event\SchemaLoad',
            ],
        ]);
    }
}
