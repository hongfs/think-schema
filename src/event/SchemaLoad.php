<?php
declare (strict_types = 1);

namespace Hongfs\ThinkSchema\event;

use think\facade\Console;

class SchemaLoad
{
    public function handle()
    {
        $db = app()->db->connect();

        $db_name = $db->getConfig('database');

        $cache_key = md5(sprintf('%s.%s', $db_name, 'schema'));

        $info = cache($cache_key);

        if(!$info) {
            Console::call('schema:load');
            return $this->handle();
        }

        (new \ReflectionClass($db))
            ->getProperty('info')
            ->setValue($db, json_decode($info, true));

        return true;
    }
}
