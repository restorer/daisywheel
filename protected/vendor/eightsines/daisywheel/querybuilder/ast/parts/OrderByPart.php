<?php

namespace daisywheel\querybuilder\ast\parts;

use daisywheel\querybuilder\ast\Part;
use daisywheel\querybuilder\ast\expr\ColumnExpr;

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
     */
    public function buildPart($swapDirection = false)
    {
        return $this->column->buildExpr() . (($swapDirection ? !$this->asc : $this->asc) ? ' ASC' : ' DESC');
    }
}
