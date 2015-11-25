<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/25
 * Time: 19:04
 */

namespace Xaircraft\ERM;


use Xaircraft\Container;
use Xaircraft\Core\Strings;
use Xaircraft\Database\TableQuery;
use Xaircraft\Database\TableSchema;
use Xaircraft\DB;
use Xaircraft\DI;

abstract class Model extends Container
{
    /**
     * @var TableSchema
     */
    private $schema;

    /**
     * @var Entity
     */
    private $entity;

    public function beforeSave()
    {
    }

    public function afterSave()
    {
    }

    public function beforeDelete()
    {
    }

    public function afterDelete($fields)
    {
    }

    public function beforeForceDelete()
    {
    }

    public function afterForceDelete($fields)
    {
    }

    public function isExists()
    {
        return $this->entity->isExist();
    }

    public function fields()
    {
        return $this->entity->getData();
    }

    public function save()
    {
        return DB::transaction(function () {
            $this->beforeSave();
            $result = $this->entity->save();
            $this->afterSave();

            return $result;
        });
    }

    public function delete()
    {
        return DB::transaction(function () {
            $this->beforeDelete();
            $key = $this->schema->autoIncrementColumn;
            $result = DB::table($this->schema->tableName)
                ->where($key, $this->entity->$key)
                ->delete()->execute();
            $this->afterDelete($this->fields());
            return $result;
        });
    }

    public function forceDelete()
    {
        return DB::transaction(function () {
            $this->beforeForceDelete();
            $key = $this->schema->autoIncrementColumn;
            $result = DB::table($this->schema->tableName)
                ->where($key, $this->entity->$key)
                ->hardDelete()->execute();
            $this->afterForceDelete($this->fields());
            return $result;
        });
    }

    private function initializeModel($table)
    {
        $this->schema = DB::table($table)->getTableSchema();
        $this->entity = DB::entity($table);
    }

    private function loadData(TableQuery $query)
    {
        $this->entity = DB::entity($query);
    }

    /**
     * @return Model
     */
    public static function model()
    {
        $table = get_called_class();
        /**
         * @var Model $model
         */
        $model = DI::get($table);
        $model->initializeModel(Strings::camelToSnake($table));
        return $model;
    }

    public static function find($arg)
    {
        $model = self::model();
        if ($arg instanceof TableQuery) {
            $query = $arg;
        } else if (is_numeric($arg)) {
            $query = DB::table($model->schema->tableName)
                ->where($model->schema->autoIncrementColumn, $arg)
                ->select();
        } else {
            throw new \Exception("What do you want to find? ");
        }

        $model->loadData($query);

        return $model;
    }

    public function __get($field)
    {
        return $this->entity->$field;
    }

    public function __set($field, $value)
    {
        $this->entity->$field = $value;
    }
}