<?php

namespace daisywheel\querybuilder\ast\commands;

use daisywheel\querybuilder\BuildException;
use daisywheel\querybuilder\BuildHelper;
use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\Expr;
use daisywheel\querybuilder\ast\expr\ColumnExpr;
use daisywheel\querybuilder\ast\parts\OrderByPart;
use daisywheel\querybuilder\ast\parts\TableAliasPart;
use daisywheel\querybuilder\ast\parts\JoinPart;
use daisywheel\querybuilder\ast\parts\TablePart;
use daisywheel\querybuilder\ast\parts\UnionPart;

class SelectCommand implements Command, Expr
{
    /** @var BuildSpec */
    protected $spec;

    /** @var boolean */
    protected $distinct = false;

    /** @var Expr[] */
    protected $columns = [];

    /** @var TableAliasPart[] */
    protected $fromList = [];

    /** @var JoinPart[] */
    protected $joinList = [];

    /** @var Expr|null */
    protected $where = null;

    /** @var ColumnExpr[] */
    protected $groupByList = [];

    /** @var Expr|null */
    protected $having = null;

    /** @var OrderByPart[] */
    protected $orderByList = [];

    /** @var int|null */
    protected $limit = null;

    /** @var int|null */
    protected $offset = null;

    /** @var UnionPart[] */
    protected $unionList = [];

    /**
     * @param BuildSpec $spec
     * @param Expr[] $columns
     */
    public function __construct($spec, $columns)
    {
        $this->spec = $spec;
        $this->columns = $columns;
    }

    /**
     * @param Expr|Expr[] $columns
     * @throws BuildException
     * @return self
     */
    public function columns($columns)
    {
        if (empty($columns)) {
            throw new BuildException('At least one column required');
        }

        $this->columns = array_merge($this->columns, BuildHelper::arg($columns));
        return $this;
    }

    /**
     * @param boolean $distinct
     * @return self
     */
    public function distinct($distinct = true)
    {
        $this->distinct = $distinct;
        return $this;
    }

    /**
     * @param string|TablePart $table
     * @param string|null $alias
     * @return self
     */
    public function from($table, $alias = null)
    {
        $this->fromList[] = new TableAliasPart($this->spec, TablePart::create($this->spec, $table), $alias);
        return $this;
    }

    /**
     * @param string|TablePart $table
     * @param string|null $alias
     * @return JoinPart
     */
    public function leftJoin($table, $alias = null)
    {
        $result = new JoinPart($this, new TableAliasPart($this->spec, TablePart::create($this->spec, $table), $alias), JoinPart::TYPE_LEFT);
        $this->joinList[] = $result;
        return $result;
    }

    /**
     * @param string|TablePart $table
     * @param string|null $alias
     * @return JoinPart
     */
    public function innerJoin($table, $alias = null)
    {
        $result = new JoinPart($this, new TableAliasPart($this->spec, TablePart::create($this->spec, $table), $alias), JoinPart::TYPE_INNER);
        $this->joinList[] = $result;
        return $result;
    }

    /**
     * @param string|TablePart $table
     * @param string|null $alias
     * @return JoinPart
     */
    public function rightJoin($table, $alias = null)
    {
        $result = new JoinPart($this, new TableAliasPart($this->spec, TablePart::create($this->spec, $table), $alias), JoinPart::TYPE_RIGHT);
        $this->joinList[] = $result;
        return $result;
    }

    /**
     * @param Expr $expr
     * @return self
     */
    public function where($expr)
    {
        $this->where = $expr;
        return $this;
    }

    /**
     * @param ColumnExpr $column
     * @return self
     */
    public function groupBy($column)
    {
        $this->groupByList[] = $column;
        return $this;
    }

    /**
     * @param Expr $expr
     * @return self
     */
    public function having($expr)
    {
        $this->having = $expr;
        return $this;
    }

    /**
     * @param ColumnExpr $column
     * @param boolean $asc
     * @return self
     */
    public function orderBy($column, $asc = true)
    {
        $this->orderByList[] = new OrderByPart($column, $asc);
        return $this;
    }

    /**
     * @param int|null $limit
     * @return self
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int|null $offset
     * @return self
     */
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param SelectCommand $command
     * @return self
     */
    public function union($command)
    {
        $this->unionList[] = new UnionPart($command, false);
        return $this;
    }

    /**
     * @param SelectCommand $command
     * @return self
     */
    public function unionAll($command)
    {
        $this->unionList[] = new UnionPart($command, true);
        return $this;
    }

    /**
     * @param string $afterColumnsSql
     * @throws BuildException
     * @return string
     */
    public function buildSql($afterColumnsSql = '')
    {
        if (empty($this->columns)) {
            throw new BuildException('At least one column required');
        }

        if ($this->offset !== null && $this->limit === null) {
            throw new BuildException('Offset without limit is not supported');
        }

        return $this->spec->buildSelectSql(
            'SELECT ' . ($this->distinct ? 'DISTINCT ' : ''),
            join(', ', array_map(/** @return string */ function ($v) {
                return $v->buildExpr();
            }, $this->columns))
                . $afterColumnsSql
                . (empty($this->fromList) ? '' : (' FROM ' . join(', ', array_map(/** @return string */ function ($v) {
                    return $v->buildPart();
                }, $this->fromList))))
                . (empty($this->joinList) ? '' : (' ' . join(' ', array_map(/** @return string */ function ($v) {
                    return $v->buildPart();
                }, $this->joinList))))
                . ($this->where === null ? '' : " WHERE {$this->where->buildExpr()}")
                . (empty($this->groupByList) ? '' : (' GROUP BY ' . join(', ', array_map(/** @return string */ function ($v) {
                    return $v->buildExpr();
                }, $this->groupByList))))
                . ($this->having === null ? '' : " HAVING {$this->having->buildExpr()}")
            ,
            $this->orderByList,
            $this->limit,
            $this->offset
        ) . join('', array_map(function ($v) {
            return " {$v->buildPart()}";
        }, $this->unionList));
    }

    /**
     * @see Command::build()
     */
    public function build()
    {
        return [$this->buildSql()];
    }

    /**
     * @see Expr::buildExpr()
     */
    public function buildExpr()
    {
        return "({$this->buildSql()})";
    }

    /**
     * @param OrderByPart[] $orderByList
     * @param boolean $swapDirection
     * @return string
     */
    public static function buildOrderBy($orderByList, $swapDirection = false)
    {
        if (empty($orderByList)) {
            return '';
        }

        return ' ORDER BY ' . join(', ', array_map(function ($v) use ($swapDirection) {
            return $v->buildPart($swapDirection);
        }, $orderByList));
    }
}
