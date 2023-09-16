<?php
declare (strict_types = 1);

namespace Hongfs\ThinkSchema\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;

class SchemaLoad extends Command
{
    protected $ref = null;

    protected $db = null;

    protected function configure()
    {
        // 指令配置
        $this->setName('schemaload')
            ->setDescription('the schemaload command');
    }

    protected function execute(Input $input, Output $output)
    {
        $this->db = app()->db->connect();

        $db_name = $this->db->getConfig('database');

        $this->ref = new \ReflectionClass($this->db);

        $cache_key = md5(sprintf('%s.%s', $db_name, 'schema'));

        $sql = sprintf('SELECT `TABLE_SCHEMA` as `db`, `TABLE_NAME` as `name` FROM `information_schema`.`TABLES` WHERE TABLE_SCHEMA="%s" AND TABLE_TYPE="BASE TABLE" AND ENGINE="InnoDB";', $db_name);

        $data = Db::query($sql);

        $schema = [];

        foreach ($data as $table) {
            $name = sprintf('%s.%s', $table['db'], $table['name']);

            $sql = sprintf('SELECT `COLUMN_NAME` as `id`, `COLUMN_TYPE` AS `Type`, `COLLATION_NAME` as `collation`, `IS_NULLABLE` as `null`, `COLUMN_KEY` as `key`, `COLUMN_DEFAULT` as `default`, `EXTRA` as `extra`, `PRIVILEGES` as `privileges`, `COLUMN_COMMENT` as `comment` from `information_schema`.`COLUMNS` WHERE TABLE_SCHEMA="%s" AND TABLE_NAME="%s" ORDER BY `ORDINAL_POSITION` ASC;', $table['db'], $table['name']);

            $schema[$name] = $this->schema_format(Db::query($sql));
        }

        cache($cache_key, json_encode($schema), null, 'schema');

        $output->writeln('success');
    }

    protected function schema_format($data) :array
    {
        $info = [
            'fields' => [],
            'type' => [],
            'bind' => [],
            'pk' => null,
            'autoinc' => null,
        ];

        foreach ($data as $field) {
            $id = $field['id'];

            $info['fields'][] = $id;

            $info['type'][$id] = $this->ref->getMethod('getFieldType')
                ->invoke($this->db, $field['Type']);

            $info['bind'][$id] = $this->ref->getMethod('getFieldBindType')
                ->invoke($this->db, $field['Type']);

            if(isset($field['key'])) {
                if(!$info['pk'] && $field['key'] === 'PRI') {
                    $info['pk'] = $id;
                }
            }

            if(isset($field['extra'])) {
                if(!$info['autoinc'] && $field['extra'] === 'auto_increment') {
                    $info['autoinc'] = $id;
                }
            }
        }

        return $info;
    }
}
