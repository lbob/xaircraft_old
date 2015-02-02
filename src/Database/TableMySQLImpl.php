<?php

namespace Xaircraft\Database;
use Xaircraft\DB;
use Xaircraft\Exception\InvalidColumnExecption;


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
        if ($this->isHasTable) {
            return $this->parseHasTable();
        }
        if ($this->isDropTable) {
            return $this->parseDropTable();
        }
        if ($this->isHasColumn) {
            return $this->parseHasColumn();
        }
        if ($this->isRenameTable) {
            return $this->parseRenameTable();
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
            if ($this->isHasTable) {
                $result = false;
                $query = DB::query($query);
                foreach ($query as $item) {
                    if (isset($item['TABLE_NAME']) && $item['TABLE_NAME'] === $this->name) {
                        $result = true;
                        break;
                    }
                }
                return $result;
            }
            if ($this->isHasColumn) {
                $result = false;
                $query = DB::query($query);
                foreach ($query as $item) {
                    if (isset($item['Field']) && $item['Field'] === $this->hasColumnName) {
                        $result = true;
                        break;
                    }
                }
                return $result;
            }
            $result = DB::statement($query);
            if (isset($this->schema))
                $this->schema->rewriteCache();
            return $result;
        }
        return false;
    }

    private function parseCreateTable()
    {
        if (!isset($this->name)) {
            throw new \InvalidArgumentException("Undefined table name.");
        }

        $result = array();
        $result[] = "CREATE TABLE IF NOT EXISTS `$this->dbName`.`" . $this->name . "`";
        if (isset($this->columns)) {
            $result[] = '(';
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
            $result[] = ")";
        }
        return implode(' ', $result);
    }

    private function parseModifyTable()
    {
        if (!isset($this->name)) {
            throw new \InvalidArgumentException("Undefined table name.");
        }

        $result = array();
        $result[] = "ALTER TABLE `$this->dbName`.`$this->name`";
        if (isset($this->columns)) {
            $modifies = array();
            if (isset($this->dropColumns) && !empty($this->dropColumns)) {
                foreach ($this->dropColumns as $item) {
                    if (array_search($item, $this->schema->getFields()) === false) {
                        throw new \InvalidArgumentException("Undefined column [$item] in table [$this->name]");
                    }
                    $modifies[] = "DROP COLUMN `$item`";
                }
            }
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

    private function parseHasTable()
    {
        return "SELECT `TABLE_NAME` FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_SCHEMA` = '$this->dbName' AND `TABLE_NAME` = '$this->name' LIMIT 1";
    }

    private function parseDropTable()
    {
        if ($this->isDropTableIfExists)
            return "DROP TABLE IF EXISTS `$this->dbName`.`$this->name`;";
        else
            return "DROP TABLE `$this->dbName`.`$this->name`;";
    }

    private function parseHasColumn()
    {
        return "DESCRIBE $this->name $this->hasColumnName";
    }

    private function parseRenameTable()
    {
        return "RENAME TABLE `$this->name` TO `$this->renameTableNewName`";
    }
}

 