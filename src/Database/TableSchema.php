<?php

namespace Xaircraft\Database;
use Whoops\Example\Exception;
use Xaircraft\App;
use Xaircraft\DB;
use Xaircraft\Exception\InvalidColumnExecption;


/**
 * Class TableMeta
 * 管理数据表的元数据定义，为了提高性能，读取的元数据将进行缓存。
 * 初期仅实现把数据缓存到文件，后续可以把数据缓存到内存中。
 *
 * @package Xaircraft\Database
 * @author lbob created at 2014/12/25 15:55
 */
class TableSchema
{

    const TYPE_PK = 'pk';
    const TYPE_BIGPK = 'bigpk';
    const TYPE_STRING = 'string';
    const TYPE_TEXT = 'text';
    const TYPE_SMALLINT = 'smallint';
    const TYPE_INTEGER = 'integer';
    const TYPE_BIGINT = 'bigint';
    const TYPE_FLOAT = 'float';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_DATETIME = 'datetime';
    const TYPE_TIMESTAMP = 'timestamp';
    const TYPE_TIME = 'time';
    const TYPE_DATE = 'date';
    const TYPE_BINARY = 'binary';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_MONEY = 'money';

    public $tableName;
    public $primaryKey;
    public $autoIncrementColumn;

    private $fields = array();
    private $columnTypes = array();
    private $types = array();
    private $dbTypes = array();
    private $lengths = array();
    private $phpTypes = array();
    private $unsigned = array();
    private $collations = array();
    private $nulls = array();
    private $keys = array();
    private $defaults = array();
    private $extras = array();
    //private $privileges = array();
    private $comments = array();
    private $validations = array();

    private $isLoad = false;
    private $source;

    private static $instances = array();

    private function __construct($tableName)
    {
        if (!isset($tableName))
            throw new \InvalidArgumentException("Invalid table name.");

        $this->tableName = $tableName;

        $this->source = App::getInstance()->getPath('schema') . '/' . $this->tableName . '.dat';

        if (!$this->isLoad) {
            $this->isLoad = true;
            if (!$this->isCached()) {
                $this->loadFromDatabase();
                $this->writeCache();
            } else {
                $this->loadFromCache();
            }
        }
        //var_dump($this->validations);
    }

    /**
     * @param $tableName
     * @return TableSchema
     */
    public static function load($tableName)
    {
        if (!isset(self::$instances[$tableName])) {
            self::$instances[$tableName] = new TableSchema($tableName);
        }
        return self::$instances[$tableName];
    }

    public function getTypes()
    {
        return $this->types;
    }

    private function loadFromDatabase()
    {
        $columns = DB::query('SHOW FULL COLUMNS FROM ' . $this->tableName);
        if (isset($columns)) {
            $this->clear();
            foreach ($columns as $row) {
                $field                     = $row['Field'];
                $this->fields[]            = $field;
                $this->columnTypes[$field] = $row['Type'];
                $this->types[$field]       = $this->getColumnType($row['Type'], $field);
                $phpType                   = $this->getColumnPhpType($this->types[$field], $field);
                $this->phpTypes[$field]    = $phpType;
                $this->collations[$field]  = $row['Collation'];
                $this->nulls[$field]       = $row['Null'];
                $this->keys[$field]        = $row['Key'];
                $this->defaults[$field]    = $row['Default'];
                $this->extras[$field]      = $row['Extra'];
                //$this->privileges[$field] = $row['Privileges'];
                $this->comments[$field] = $row['Comment'];

                $isNullable                = $row['Null'] === 'YES';
                $enumDefine                = stripos($row['Type'], 'enum') !== false ? $row['Type'] : null;
                $this->validations[$field] = new Validation($field, $phpType, $isNullable, $enumDefine, $row['Comment']);

                if ($row['Key'] === 'PRI') {
                    $this->primaryKey[] = $field;
                }
                if ($row['Extra'] === 'auto_increment') {
                    $this->autoIncrementColumn = $field;
                }
            }
        } else {
            throw new \Exception("Table undefined [$this->tableName].");
        }
    }

    private function loadFromCache()
    {
        if (file_exists($this->source)) {
            /**
             * @var $meta TableSchema
             */
            $meta              = unserialize(file_get_contents($this->source));
            $this->fields      = $meta->fields;
            $this->columnTypes = $meta->columnTypes;
            $this->types       = $meta->types;
            $this->dbTypes     = $meta->dbTypes;
            $this->lengths     = $meta->lengths;
            $this->phpTypes    = $meta->phpTypes;
            $this->collations  = $meta->collations;
            $this->nulls       = $meta->nulls;
            $this->keys        = $meta->keys;
            $this->defaults    = $meta->defaults;
            $this->extras      = $meta->extras;
            //$this->privileges = $meta->privileges;
            $this->comments            = $meta->comments;
            $this->unsigned            = $meta->unsigned;
            $this->validations         = $meta->validations;
            $this->primaryKey          = $meta->primaryKey;
            $this->autoIncrementColumn = $meta->autoIncrementColumn;
        }
    }

    private function writeCache()
    {
        $dir = dirname($this->source);
        if (!file_exists($dir)) {
            \Xaircraft\Common\IO::makeDir($dir);
        }
        $handler = fopen($this->source, 'w+');
        fwrite($handler, serialize($this));
        fclose($handler);
    }

    private function isCached()
    {
        return file_exists($this->source);
    }

    public function rewriteCache()
    {
        $this->loadFromDatabase();
        $this->writeCache();
    }

    private function clear()
    {
        unset($this->primaryKey);
        unset($this->autoIncrementColumn);

        $this->fields = array();
        $this->columnTypes = array();
        $this->types = array();
        $this->dbTypes = array();
        $this->lengths = array();
        $this->phpTypes = array();
        $this->unsigned = array();
        $this->collations = array();
        $this->nulls = array();
        $this->keys = array();
        $this->defaults = array();
        $this->extras = array();
        //private $privileges = array();
        $this->comments = array();
        $this->validations = array();
    }

    /**
     * 从数据库字段类型转化为抽象数据类型
     * @param $type
     * @param $field
     * @return mixed
     */
    private function getColumnType($type, $field)
    {
        static $typeMaps = array(
            'tinyint'    => self::TYPE_SMALLINT,
            'bit'        => self::TYPE_INTEGER,
            'smallint'   => self::TYPE_SMALLINT,
            'mediumint'  => self::TYPE_INTEGER,
            'int'        => self::TYPE_INTEGER,
            'integer'    => self::TYPE_INTEGER,
            'bigint'     => self::TYPE_BIGINT,
            'float'      => self::TYPE_FLOAT,
            'double'     => self::TYPE_FLOAT,
            'real'       => self::TYPE_FLOAT,
            'decimal'    => self::TYPE_DECIMAL,
            'numeric'    => self::TYPE_DECIMAL,
            'tinytext'   => self::TYPE_TEXT,
            'mediumtext' => self::TYPE_TEXT,
            'longtext'   => self::TYPE_TEXT,
            'longblob'   => self::TYPE_BINARY,
            'blob'       => self::TYPE_BINARY,
            'text'       => self::TYPE_TEXT,
            'varchar'    => self::TYPE_STRING,
            'string'     => self::TYPE_STRING,
            'char'       => self::TYPE_STRING,
            'datetime'   => self::TYPE_DATETIME,
            'year'       => self::TYPE_DATE,
            'date'       => self::TYPE_DATE,
            'time'       => self::TYPE_TIME,
            'timestamp'  => self::TYPE_TIMESTAMP,
            'enum'       => self::TYPE_STRING,
        );

        if (isset($typeMaps[$type])) {
            return $typeMaps[$type];
        } else if (preg_match('/^(\w+)\((.+?)\)(.*)$/', $type, $matches)) {
            if (isset($typeMaps[$matches[1]])) {
                $this->unsigned[$field] = isset($matches[3]) && stripos($matches[3], 'unsigned') !== false;
                $this->dbTypes[$field] = $matches[1];
                if (isset($matches[2])) {
                    $this->lengths[$field] = $matches[2] + 0;
                }
                return preg_replace('/\(.+\)/', '(' . $matches[2] . ')', $typeMaps[$matches[1]]);
            }
        } else if (preg_match('/^(\w+)\s+/', $type, $matches)) {
            if (isset($typeMaps[$matches[1]]))
                return preg_replace('/^\w+/', $typeMaps[$matches[1]], $type);
        }
        return $type;
    }

    /**
     * 从抽象数据类型转化为PHP数据类型
     * @param $columnType
     * @param $field
     * @return string
     */
    private function getColumnPhpType($columnType, $field)
    {
        static $typeMaps = array(
            'smallint' => 'integer',
            'integer'  => 'integer',
            'bigint'   => 'integer',
            'boolean'  => 'boolean',
            'float'    => 'double',
            'binary'   => 'resource'
        );

        // 除了上面的映射关系外，还有几个特殊情况：
        // 1. bigint字段，在64位环境下，且为singed时，使用integer来表示，否则string
        // 2. integer字段，在32位环境下，且为unsinged时，使用string表示，否则integer
        // 3. 映射中不存在的字段类型均使用string
        if (isset($typeMaps[$columnType])) {
            return $typeMaps[$columnType];
        } else {
            return 'string';
        }
    }

    public function phpTypecast($columnName, $columnValue)
    {
        $phpType = $this->phpTypes[$columnName];
        if ($columnValue === '' && $phpType !== self::TYPE_TEXT && $phpType !== self::TYPE_STRING && $phpType !== self::TYPE_BINARY) {
            return null;
        }
        // 内容为null，或者 $value 的类型与PHP类型一致，或者 $value 是一个数据库表达式，
        // 那么可以直接返回
        if ($columnValue === null || gettype($columnValue) === $phpType) {
            return $columnValue;
        }

        // 否则，需要根据PHP类型来完成类型转换
        switch ($phpType) {
            case 'resource':
            case 'string':
                return is_resource($columnValue) ? $columnValue : (string)$columnValue;
            case 'integer':
                return $columnValue + 0;
            case 'boolean':
                return (bool)$columnValue;
            case 'double':
                return (double)$columnValue;
        }
        return $columnValue;
    }

    private function validColumn($columnName, $columnValue)
    {
        if (!array_key_exists($columnName, $this->validations)) {
            throw new InvalidColumnExecption("[$this->tableName].[$columnName] : Undefined field.",
                InvalidColumnExecption::INVALID_COLUMN_ERROR_CODE,
                array('field' => $columnName));
        }
        /**
         * @var $validation Validation
         */
        $validation = $this->validations[$columnName];
        list($isSuccess, $message) = $validation->valid($columnValue);
        if (!$isSuccess)
            throw new InvalidColumnExecption("[$this->tableName].[$columnName] : $message.",
                InvalidColumnExecption::INVALID_COLUMN_ERROR_CODE,
                array('field' => $columnName));
    }

    public function valid($columns, $isUpdateQuery = false)
    {
        if (isset($columns)) {
            foreach ($columns as $key => $value) {
                $this->validColumn($key, $value);
            }
            $nullValidColumns = $this->nulls;
            if (isset($this->autoIncrementColumn)) {
                unset($nullValidColumns[$this->autoIncrementColumn]);
            }
            foreach ($nullValidColumns as $key => $value) {
                if ($value === 'NO') {
                    $nullErrorBreakout = false;
                    if (array_key_exists($key, $columns)) {
                        if (is_null($columns[$key]))
                            $nullErrorBreakout = true;
                    } else {
                        if (!$isUpdateQuery)
                            $nullErrorBreakout = true;
                    }

                    if ($nullErrorBreakout) {
                        throw new InvalidColumnExecption("[$this->tableName].[$key] : can't be null.",
                            InvalidColumnExecption::INVALID_COLUMN_ERROR_CODE,
                            array('field' => $key));
                    }
                }
            }
        }
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getColumnInfo($columnName)
    {
        if (array_search($columnName, $this->fields) !== false) {
            $name = $columnName;
            $type = $this->dbTypes[$columnName];
            $length = isset($this->lengths[$columnName]) ? $this->lengths[$columnName] : -1;
            $isUnsigned = $this->unsigned[$columnName] ? true : false;
            $isIncrements = isset($this->autoIncrementColumn) && $this->autoIncrementColumn === $columnName ? true : false;
            $isNullable = $this->nulls[$columnName] === 'YES' ? true : false;
            $isPrimaryKey = $this->keys[$columnName] === 'PRI' ? true : false;
            $defaultValue = $this->defaults[$columnName];
            $comments = $this->comments[$columnName];

            return array(
                'name' => $name,
                'type' => strtoupper($type),
                'length' => $length,
                'isUnsigned' => $isUnsigned,
                'isIncrements' => $isIncrements,
                'isNullable' => $isNullable,
                'isPrimaryKey' => $isPrimaryKey,
                'default' => $defaultValue,
                'comments' => $comments
            );
        }
    }
}

 