<?php

namespace daisywheel\querybuilder\ast\parts;

use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\Expr;
use daisywheel\querybuilder\ast\Part;

class SetPart implements Part
{
    /** @var BuildSpec */
    protected $spec;

    /** @var string */
    protected $columnName;

    /** @var Expr */
    protected $expr;

    /**
     * @param BuildSpec $spec
     * @param string $columnName
     * @param Expr $expr
     */
    public function __construct($spec, $columnName, $expr)
    {
        $this->spec = $spec;
        $this->columnName = $columnName;
        $this->expr = $expr;
    }

    /**
     * @see Part::buildPart()
     */
    public function buildPart($swapDirection = false)
    {
        return "{$this->spec->quoteIdentifier($this->columnName)} = {$this->expr->buildExpr()}";
    }
}
