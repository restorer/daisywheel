<?php

namespace daisywheel\querybuilder\ast\parts;

use daisywheel\querybuilder\ast\expr\ColumnExpr;
use daisywheel\querybuilder\ast\Part;

class OrderByPart implements Part
{
    /** @var ColumnExpr */
    protected $column;

    /** @var boolean */
    protected $asc;

    /**
     * @param ColumnExpr $column
     * @param boolean $asc
     */
    public function __construct($column, $asc)
    {
        $this->column = $column;
        $this->asc = $asc;
    }

    /**
     * @see Part::buildPart()
     *
     * @param bool $swapDirection optional, default to false
     *
     * @return string
     */
    public function buildPart($swapDirection = false)
    {
        /** @noinspection NestedTernaryOperatorInspection */
        return $this->column->buildExpr() . (($swapDirection ? !$this->asc : $this->asc) ? ' ASC' : ' DESC');
    }
}
