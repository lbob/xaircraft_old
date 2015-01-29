<?php

namespace Xaircraft\Database;
use Xaircraft\DB;


/**
 * Class TableMySQLImpl
 *
 * @package Xaircraft\Database
 * @author lbob created at 2015/1/29 10:17
 */
class TableMySQLImpl extends TableBase{

    /**
     * @return string
     */
    public function toString()
    {
        if ($this->isCreateTable) {
            return $this->parseCreateTable();
        }
        if ($this->isModifyTable) {
            return $this->parseModifyTable();
        }
    }

    /**
     * 执行命令
     * @return boolean
     */
    public function execute()
    {
        $query = $this->toString();

        if (isset($query)) {
            return DB::statement($query);
        }
        return false;
    }

    private function parseCreateTable()
    {
        if (!isset($this->name)) {
            throw new \InvalidArgumentException("Undefined table name.");
        }

        $result = array();
        $result[] = "CREATE TABLE IF NOT EXISTS `" . $this->name . "` (";
        if (isset($this->columns)) {
            $columns = array();
            foreach ($this->columns as $item) {
                $column = $item['column'];
                $columns[] = $column->toString();
                if ($column->isUnique()) {
                    $this->uniqueColumns[] = $column;
                }
                if ($column->isPrimaryKey()) {
                    $this->primaryKeyColumns[] = $column;
                }
            }
            if (isset($this->primaryKeyColumns) && !empty($this->primaryKeyColumns)) {
                $primaryKeyColumnNames = array();
                foreach ($this->primaryKeyColumns as $column) {
                    $primaryKeyColumnNames[] = "`" . $column->getColumnName() . "`";
                }
                $columns[] = "PRIMARY KEY(" . implode(',', $primaryKeyColumnNames) . ")";
            }
            if (isset($this->uniqueColumns) && !empty($this->uniqueColumns)) {
                foreach ($this->uniqueColumns as $column) {
                    $columns[] = "UNIQUE INDEX `" . $column->getColumnName() . "_UNIQUE` (`" . $column->getColumnName() . "` ASC)";
                }
            }
            $result[] = implode(',', $columns);
        }
        $result[] = ")";
        return implode(' ', $result);
    }

    private function parseModifyTable()
    {
        if (!isset($this->name)) {
            throw new \InvalidArgumentException("Undefined table name.");
        }

        $result = array();
        $result[] = "ALTER TABLE `$this->name`";
        if (isset($this->columns)) {
            $modifies = array();
            foreach ($this->columns as $item) {
                $type = $item['type'];
                $columnName = $item['name'];
                $column = $item['column'];
                if ($type === self::ALTER_TABLE_ADD_COLUMN) {
                    $modifies[] = $type . ' ' . $column->toString();
                }
                if ($type === self::ALTER_TABLE_CHANGE_COLUMN) {
                    $modifies[] = $type . " `" . $columnName . "` " . $column->toString();
                }
            }
            $result[] = implode(',', $modifies);
        }

        return implode(' ', $result);
    }
}

 