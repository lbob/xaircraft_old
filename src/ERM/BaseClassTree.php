<?php
namespace Xaircraft\ERM;
use Xaircraft\DB;

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

        if (!isset($query) || empty($query)) {
            return null;
        }

        foreach ($query as $item) {
            $item['subTree'] = $this->getTree($showColumns, $item['classNo']);
            $brothers[] = $item;
        }

        return $brothers;
    }
}

 