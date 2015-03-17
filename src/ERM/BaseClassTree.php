<?php
namespace Xaircraft\ERM;
use Xaircraft\DB;
use Xaircraft\Exception\StatusException;

/**
 * Class BaseClassTree
 *
 * @author lbob created at 2015/1/13 10:09
 */
class BaseClassTree {

    private $tableName;
    private $classColumnName;
    private $classNoLength;

    public function __construct($tableName, $classColumnName, $classNoLength = 4)
    {
        $this->tableName = $tableName;
        $this->classColumnName = $classColumnName;
        $this->classNoLength = $classNoLength;
    }

    public function getNextClassNo($parentClassNo = '')
    {
        if (isset($parentClassNo) && $parentClassNo !== '') {
            if (strlen($parentClassNo) % $this->classNoLength !== 0) {
                throw new \Exception("编号错误");
            }
        }

        $lastClassNo = \Xaircraft\DB::table($this->tableName)
            ->where($this->classColumnName, 'LIKE', $parentClassNo . "%")
            ->where('LENGTH(' . $this->classColumnName . ')', strlen($parentClassNo) + 4)
            ->orderBy($this->classColumnName, 'DESC')
            ->pluck($this->classColumnName)
            ->execute();
        $nextClassValue = 1;
        if (isset($lastClassNo)) {
            $lastClassNo = substr($lastClassNo, strlen($lastClassNo) - $this->classNoLength + 1, $this->classNoLength);
            $nextClassValue = $lastClassNo + 1;
        }
        $nextClassNo = $nextClassValue . '';
        $len = strlen($nextClassNo);
        if ($len < $this->classNoLength) {
            for ($i = 1; $i <= $this->classNoLength - $len; $i++) {
                $nextClassNo = '0' . $nextClassNo;
            }
        }
        return $parentClassNo . $nextClassNo;
    }

    public function getTree(array $showColumns = null, $parentClassNo = '', $isSort = false, $sortColumnName = 'sort')
    {
        $brothers = array();
        $query = DB::table($this->tableName)
            ->where($this->classColumnName, 'LIKE', $parentClassNo . "%")
            ->where('LENGTH(' . $this->classColumnName . ')', strlen($parentClassNo) + 4)
            ->select($showColumns);

        if ($isSort) {
            $query = $query->orderBy($sortColumnName, 'ASC')->orderBy($this->classColumnName, 'ASC');
        } else {
            $query = $query->orderBy($this->classColumnName, 'ASC');
        }

        $result = $query->execute();

        if (!isset($result) || empty($result)) {
            return null;
        }

        foreach ($result as $item) {
            $item['level'] = strlen($item['classNo']) / $this->classNoLength;
            $item['subTree'] = $this->getTree($showColumns, $item['classNo'], $isSort, $sortColumnName);
            $brothers[] = $item;
        }

        return $brothers;
    }

    public function moveTreeNodeAndSave($classNo, $moveToParentClassNo, $otherSaveHandler = null)
    {
        if (isset($moveToParentClassNo) && $moveToParentClassNo !== '') {
            if (strlen($moveToParentClassNo) % $this->classNoLength !== 0) {
                throw new \Exception("编号错误");
            }
        }

        try {
            DB::beginTransaction();
            $currentParentClassNo = substr($classNo, 0, strlen($classNo) - $this->classNoLength);
            if (isset($moveToParentClassNo) && $moveToParentClassNo !== $currentParentClassNo) {
                if ($classNo === $moveToParentClassNo) {
                    throw new \Exception("节点不允许为自身的父节点");
                }
                if (strlen($classNo) < strlen($moveToParentClassNo) && substr($moveToParentClassNo, 0, strlen($classNo)) === $classNo) {
                    throw new \Exception("节点自身的子节点不允许为父节点");
                }
                if (isset($moveToParentClassNo) && $moveToParentClassNo !== '' && DB::table($this->tableName)->where($this->classColumnName, $moveToParentClassNo)->count()->execute() <= 0) {
                    throw new \Exception("找不到移动的目标父节点");
                }
                $newClassNo = $this->getNextClassNo($moveToParentClassNo);
                DB::table($this->tableName)->where($this->classColumnName, $classNo)->update(array(
                    $this->classColumnName => $newClassNo
                    ))->execute();
                DB::table($this->tableName)->where($this->classColumnName, 'LIKE', $classNo . '%')->update(array(
                    $this->classColumnName => DB::raw("CONCAT('" . $newClassNo . "', SUBSTRING(" . $this->classColumnName .", " . (strlen($classNo) + 1) . "))")
                ))->execute();
            }
            if (isset($otherSaveHandler) && is_callable($otherSaveHandler)) {
                $result = call_user_func($otherSaveHandler, $classNo, isset($newClassNo) ? $newClassNo : $classNo);
            }
            DB::commit();
            if (isset($result))
                return $result;
        } catch (StatusException $status) {
            DB::rollback();
            throw $status;
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    public function getSimpleList($titleColumnName, array $showColumns = null, $levelIndentCharacter = null)
    {
        $data = DB::table($this->tableName)->select($showColumns)->orderBy($this->classColumnName, 'ASC')->execute();

        if (isset($levelIndentCharacter)) {
            $list = array();
            foreach ($data as $row) {
                $title = $row[$titleColumnName];
                $level = strlen($row[$this->classColumnName]) / $this->classNoLength;
                if ($level > 1) {
                    for ($i = 0; $i < $level; $i++) {
                        $title = $levelIndentCharacter . $title;
                    }
                }
                $row[$titleColumnName] = $title;
                $list[] = $row;
            }
            $data = $list;
        }
        return $data;
    }
}

 