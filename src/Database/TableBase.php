<?php

namespace Xaircraft\Database;


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
    protected $columns = array();
    protected $isCreateTable = false;
    protected $isModifyTable = false;
    protected $currentColumn;
    protected $primaryKeyColumns = array();
    protected $uniqueColumns = array();
    protected $renames = array();
    /**
     * @var TableSchema
     */
    protected $schema;

    public function __construct($prefix = null)
    {
        $this->prefix = $prefix;
    }

    /**
     * 新建数据表
     * @param $name
     * @param $handler
     * @return Table
     */
    public function create($name, $handler)
    {
        $this->logicName = $name;
        $this->name = isset($this->prefix) ? $this->prefix . $name : $name;
        $this->isCreateTable = true;
        $this->isModifyTable = false;

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
        $this->logicName = $name;
        $this->name = isset($this->prefix) ? $this->prefix . $name : $name;
        $this->isCreateTable = false;
        $this->isModifyTable = true;

        $this->schema = TableSchema::load($name);

        if (isset($handler) && is_callable($handler)) {
            call_user_func($handler, $this);
            $this->saveCurrentColumn();
        }

        return $this;
    }

    /**
     * 修改数据表名称
     * @param $from
     * @param $to
     * @return Table
     */
    public function rename($from, $to)
    {
        // TODO: Implement rename() method.
    }

    /**
     * 删除数据表
     * @param $name
     * @return Table
     */
    public function drop($name)
    {
        // TODO: Implement drop() method.
    }

    /**
     * 删除数据表
     * @param $name
     * @return Table
     */
    public function dropIfExists($name)
    {
        // TODO: Implement dropIfExists() method.
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
        // TODO: Implement dropColumn() method.
    }

    /**
     * 是否存在数据表
     * @param $name
     * @return boolean
     */
    public function hasTable($name)
    {
        // TODO: Implement hasTable() method.
    }

    /**
     * 数据表是否存在字段
     * @param $tableName
     * @param $columnName
     * @return boolean
     */
    public function hasColumn($tableName, $columnName)
    {
        // TODO: Implement hasColumn() method.
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

 