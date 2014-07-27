<?php

namespace daisywheel\db\builder;

use daisywheel\core\InvalidArgumentsException;

class SelectCommand extends CommandWithAlias implements Part
{
    protected $fromInsert = null;
    protected $distinct = false;
    protected $columns = array();
    protected $fromList = array();
    protected $joinList = array();
    protected $where = null;
    protected $groupByList = array();
    protected $having = null;
    protected $orderByList = array();
    protected $offset = null;
    protected $limit = null;
    protected $unionList = array();

    public function __construct($driver, $fromInsert=null)
    {
        parent::__construct($driver);
        $this->fromInsert = $fromInsert;
    }

    public function build()
    {
        if ($this->fromInsert) {
            return $this->fromInsert->build();
        } else {
            return parent::build();
        }
    }

    public function distinct($distinct=true)
    {
        $this->distinct = $distinct;
        return $this;
    }

    public function columns()
    {
        $this->columns = array_map(function($v) {
            return ValuePart::create(array($v));
        }, func_get_args());

        return $this;
    }

    public function from()
    {
        $this->fromList[] = Table::create(func_get_args());
        return $this;
    }

    public function leftJoin()
    {
        $join = new JoinTable($this, JoinTable::TYPE_LEFT, func_get_args());
        $this->joinList[] = $join;
        return $join;
    }

    public function innerJoin()
    {
        $join = new JoinTable($this, JoinTable::TYPE_INNER, func_get_args());
        $this->joinList[] = $join;
        return $join;
    }

    public function rightJoin()
    {
        $join = new JoinTable($this, JoinTable::TYPE_RIGHT, func_get_args());
        $this->joinList[] = $join;
        return $join;
    }

    public function where($expression)
    {
        $this->where = $expression;
        return $this;
    }

    public function groupBy($column)
    {
        $this->groupByList[] = ColumnPart::create(array($column));
        return $this;
    }

    public function having($expression)
    {
        $this->having = $expression;
        return $this;
    }

    public function orderBy($column, $asc=true)
    {
        $this->orderByList[] = array(
            'column' => ColumnPart::create(array($column)),
            'asc' => $asc,
        );

        return $this;
    }

    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function union($command)
    {
        $this->unionList[] = array(
            'command' => $command,
            'all' => false,
        );

        return $this;
    }

    public function unionAll($command)
    {
        $this->unionList[] = array(
            'command' => $command,
            'all' => true,
        );

        return $this;
    }

    protected function getColumns()
    {
        return $this->columns;
    }

    protected function getDistinct()
    {
        return $this->distinct;
    }

    protected function getFromList()
    {
        return $this->fromList;
    }

    protected function getJoinList()
    {
        return $this->joinList;
    }

    protected function getWhere()
    {
        return $this->where;
    }

    protected function getGroupByList()
    {
        return $this->groupByList;
    }

    protected function getHaving()
    {
        return $this->having;
    }

    protected function getOrderByList()
    {
        return $this->orderByList;
    }

    protected function getOffset()
    {
        return $this->offset;
    }

    protected function getLimit()
    {
        return $this->limit;
    }

    protected function getUnionList()
    {
        return $this->unionList;
    }
}
