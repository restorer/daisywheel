<?php

namespace daisywheel\querybuilder\ast\commands;

use daisywheel\querybuilder\BuildException;
use daisywheel\querybuilder\BuildHelper;
use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\parts\TablePart;

class InsertCommand implements Command
{
    /** @var BuildSpec */
    protected $spec;

    /** @var TablePart */
    protected $table;

    /** @var string[] */
    protected $columns;

    /** @var array<array<Expr>> */
    protected $values = [];

    /** @var SelectCommand|null */
    protected $select = null;

    /**
     * @param BuildSpec $spec
     * @param TablePart $table
     * @param string[] $columns
     */
    public function __construct($spec, $table, $columns)
    {
        if (empty($columns)) {
            throw new BuildException('At least one column required');
        }

        $this->spec = $spec;
        $this->table = $table;
        $this->columns = $columns;
    }

    /**
     * @param Expr[]|array<array<Expr>> $valuesOrList
     * @return self
     */
    public function values($valuesOrList)
    {
        $valuesOrList = BuildHelper::args(func_get_args());

        if (empty($valuesOrList)) {
            throw new BuildException('At least one value required');
        }

        if (!is_array($valuesOrList[0])) {
            $valuesOrList = [$valuesOrList];
        }

        foreach ($valuesOrList as $item) {
            if (count($item) !== count($this->columns)) {
                throw new BuildException('Values count (' . count($item) . ') must be the same as columns count (' . count($this->columns) . ')');
            }

            $this->values[] = $item;
        }

        return $this;
    }

    /**
     * @param SelectCommand $select
     * @return self
     */
    public function select($select)
    {
        $this->select = $select;
        return $this;
    }

    /**
     * @see Command::build()
     */
    public function build()
    {
        if (empty($this->values) && $this->select === null) {
            throw new BuildException('Values or select required');
        }

        return [
            "INSERT INTO {$this->table->buildPart()} ("
            . join(', ', array_map(/** @return string */ function ($v) {
                return $this->spec->quoteIdentifier($v);
            }, $this->columns))
            . ')'
            . (empty($this->values)
                ? ''
                : (
                    ' VALUES ' . join(', ', array_map(/** @return string */ function ($items) {
                        return '(' . join(', ', array_map(/** @return string */ function ($v) {
                            return $v->buildExpr();
                        }, $items)) . ')';
                    }, $this->values))
                )
            )
            . ($this->select === null
                ? ''
                : (' ' . $this->select->buildSql())
            )
        ];
    }
}
