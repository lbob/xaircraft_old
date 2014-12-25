<?php

namespace Xaircraft\Database;
use Xaircraft\App;
use Xaircraft\DB;


/**
 * Class TableMeta
 * 管理数据表的元数据定义，为了提高性能，读取的元数据将进行缓存。
 * 初期仅实现把数据缓存到文件，后续可以把数据缓存到内存中。
 *
 * @package Xaircraft\Database
 * @author lbob created at 2014/12/25 15:55
 */
class TableMeta {

    public $tableName;
    public $primaryKey;
    public $autoIncrementColumn;

    private $fields = array();
    private $types = array();
    private $collations = array();
    private $nulls = array();
    private $keys = array();
    private $defaults = array();
    private $extras = array();
    //private $privileges = array();
    private $comments = array();

    private $isLoad = false;
    private $source;

    private static $instances = array();

    private function __construct($tableName)
    {
        if (!isset($tableName))
            throw new \InvalidArgumentException("Invalid table name.");

        $this->tableName = $tableName;

        $this->source = App::getInstance()->getPath('tableMeta') . '/' . $this->tableName . '.dat';

        if (!$this->isLoad) {
            $this->isLoad = true;
            if (!$this->isCached()) {
                $this->loadFromDatabase();
                $this->writeCache();
            } else {
                $this->loadFromCache();
            }
        }
    }

    /**
     * @param $tableName
     * @return TableMeta
     */
    public static function load($tableName)
    {
        if (!isset(self::$instances[$tableName])) {
            self::$instances[$tableName] = new TableMeta($tableName);
        }
        return self::$instances[$tableName];
    }

    public function getTypes()
    {
        return $this->types;
    }

    private function loadFromDatabase()
    {
        $columns = DB::statement('SHOW FULL COLUMNS FROM ' . $this->tableName);
        foreach ($columns as $row) {
            $field = $row['Field'];
            $this->fields[] = $field;
            $this->types[$field] = $row['Type'];
            $this->collations[$field] = $row['Collation'];
            $this->nulls[$field] = $row['Null'];
            $this->keys[$field] = $row['Key'];
            $this->defaults[$field] = $row['Default'];
            $this->extras[$field] = $row['Extra'];
            //$this->privileges[$field] = $row['Privileges'];
            $this->comments[$field] = $row['Comment'];

            if ($row['Key'] === 'PRI') {
                $this->primaryKey[] = $field;
            }
            if ($row['Extra'] === 'auto_increment') {
                $this->autoIncrementColumn = $field;
            }
        }
    }

    private function loadFromCache()
    {
        if (file_exists($this->source)) {
            /**
             * @var $meta TableMeta
             */
            $meta = unserialize(file_get_contents($this->source));
            $this->fields = $meta->fields;
            $this->types = $meta->types;
            $this->collations = $meta->collations;
            $this->nulls = $meta->nulls;
            $this->keys = $meta->keys;
            $this->defaults = $meta->defaults;
            $this->extras = $meta->extras;
            //$this->privileges = $meta->privileges;
            $this->comments = $meta->comments;
            $this->primaryKey = $meta->primaryKey;
            $this->autoIncrementColumn = $meta->autoIncrementColumn;
        }
    }

    private function writeCache()
    {
        $dir = dirname($this->source);
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        $handler = fopen($this->source, 'w+');
        fwrite($handler, serialize($this));
        fclose($handler);
    }

    private function isCached()
    {
        return file_exists($this->source);
    }
}

 