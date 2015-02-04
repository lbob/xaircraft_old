<?php

namespace Xaircraft\Database;
use Xaircraft\DB;


/**
 * Class TableBase
 *
 * @package Xaircraft\Database
 * @author lbob created at 2015/1/29 10:03
 */
abstract class TableBase implements Table {

    const ALTER_TABLE_ADD_COLUMN = 'ADD COLUMN';
    const ALTER_TABLE_CHANGE_COLUMN = 'CHANGE COLUMN';

    protected $name;
    protected $logicName;
    protected $prefix;
    protected $dbName;
    protected $columns = array();
    protected $isCreateTable = false;
    protected $isModifyTable = false;
    protected $isRenameTable = false;
    protected $isHasTable = false;
    protected $isDropTable = false;
    protected $isDropTableIfExists = false;
    protected $isHasColumn = false;
    protected $hasColumnName;
    protected $currentColumn;
    protected $primaryKeyColumns = array();
    protected $uniqueColumns = array();
    protected $renames = array();
    protected $dropColumns = array();
    protected $renameTableNewName;
    /**
     * @var TableSchema
     */
    protected $schema;

    public function __construct($dbName, $prefix = null)
    {
        $this->prefix = $prefix;
        $this->dbName = $dbName;
    }

    /**
     * 新建数据表
     * @param $name
     * @param $handler
     * @return Table
     */
    public function create($name, $handler)
    {
        $this->isCreateTable = true;
        $this->isModifyTable = false;
        $this->setTableName($name);

        if (isset($handler) && is_callable($handler)) {
            call_user_func($handler, $this);
            $this->saveCurrentColumn();
        }

        return $this;
    }

    /**
     * 更新现有数据表
     * @param $name
     * @param $handler
     * @return Table
     */
    public function table($name, $handler)
    {
        $this->isCreateTable = false;
        $this->isModifyTable = true;
        $this->setTableName($name);

        if (isset($handler) && is_callable($handler)) {
            call_user_func($handler, $this);
            $this->saveCurrentColumn();
        }

        return $this;
    }

    private function setTableName($name)
    {
        if (!isset($name)) {
            throw new \InvalidArgumentException("Undefined table name.");
        }
        $this->logicName = $name;
        $this->name = isset($this->prefix) ? $this->prefix . $name : $name;

        if (($this->isModifyTable || $this->isDropTable || $this->isRenameTable)) {
            $this->schema = TableSchema::load($this->name);
            $this->schema->rewriteCache();
        }
    }

    /**
     * 修改数据表名称
     * @param $from
     * @param $to
     * @return Table
     */
    public function rename($from, $to)
    {
        if (!isset($to)) {
            throw new \InvalidArgumentException("Undefined argument new table name.");
        }

        $this->isRenameTable = true;
        $this->setTableName($from);
        $this->renameTableNewName = isset($this->prefix) ? $this->prefix . $to : $to;

        return $this;
    }

    /**
     * 删除数据表
     * @param $name
     * @return Table
     */
    public function drop($name)
    {
        $this->isDropTable = true;
        $this->setTableName($name);

        return $this;
    }

    /**
     * 删除数据表
     * @param $name
     * @return Table
     */
    public function dropIfExists($name)
    {
        $this->isDropTable = true;
        $this->isDropTableIfExists = true;
        $this->setTableName($name);

        return $this;
    }

    /**
     * 对字段进行操作
     * @return Column
     */
    public function column()
    {
        $this->saveCurrentColumn();

        $this->currentColumn = new ColumnMySQLImpl();
        return $this->currentColumn;
    }

    private function saveCurrentColumn()
    {
        if (isset($this->currentColumn)) {
            $result = array();
            $column = $this->currentColumn;
            $columnName = $this->currentColumn->getColumnName();
            if (isset($this->renames) && array_key_exists($columnName, $this->renames)) {
                $columnName = $this->renames[$columnName];
            }
            $result['type'] = self::ALTER_TABLE_ADD_COLUMN;
            if (isset($this->schema)) {
                if (array_search($columnName, $this->schema->getFields()) !== false) {
                    $result['type'] = self::ALTER_TABLE_CHANGE_COLUMN;
                }
            }
            $result['name'] = $columnName;
            $result['column'] = $column;
            $this->columns[] = $result;
        }
    }

    /**
     * 修改字段名称
     * @param $from
     * @param $to
     * @return Table
     */
    public function renameColumn($from, $to)
    {
        $this->saveCurrentColumn();

        if ($this->isModifyTable) {
            $fromColumnInfo = $this->schema->getColumnInfo($from);
            if (!isset($fromColumnInfo)) {
                throw new \InvalidArgumentException("Undefined column [$from].");
            }

            $column = new ColumnMySQLImpl();
            $column = $column->setType($fromColumnInfo['type']);
            $column = $column->setName($to);
            if (isset($fromColumnInfo['length'])) {
                $column = $column->length($fromColumnInfo['length']);
            }
            if ($fromColumnInfo['isNullable']) {
                $column = $column->nullable();
            }
            $column = $column->defaultValue($fromColumnInfo['default']);
            $column = $column->comment($fromColumnInfo['comments']);
            $this->currentColumn = $column;
            $this->renames[$to] = $from;
        }

        return $this;
    }

    /**
     * 删除数据表字段
     * @param string|array $nameOrNames
     * @return Table
     */
    public function dropColumn($nameOrNames)
    {
        if ($this->isModifyTable && isset($nameOrNames)) {
            if (is_string($nameOrNames)) {
                $this->dropColumns[] = $nameOrNames;
            }
            if (is_array($nameOrNames)) {
                $this->dropColumns = array_merge($this->dropColumns, $nameOrNames);
            }
        }
        return $this;
    }

    /**
     * 是否存在数据表
     * @param $name
     * @return Table
     */
    public function hasTable($name)
    {
        $this->setTableName($name);
        $this->isHasTable = true;

        return $this;
    }

    /**
     * 数据表是否存在字段
     * @param $tableName
     * @param $columnName
     * @return Table
     */
    public function hasColumn($tableName, $columnName)
    {
        if (!isset($columnName)) {
            throw new \InvalidArgumentException("Undefined column name");
        }

        $this->setTableName($tableName);
        $this->isHasColumn = true;
        $this->hasColumnName = $columnName;

        return $this;
    }

    /**
     * @return string
     */
    public abstract function toString();

    /**
     * 执行命令
     * @return boolean
     */
    public abstract function execute();
}

 