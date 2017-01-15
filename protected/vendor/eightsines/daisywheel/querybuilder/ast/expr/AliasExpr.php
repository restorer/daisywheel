<?php

namespace daisywheel\querybuilder\ast\expr;

use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\Expr;

class AliasExpr implements Expr
{
    /** @var BuildSpec */
    protected $spec;

    /** @var Expr */
    protected $expr;

    /** @var string */
    protected $alias;

    /**
     * @param BuildSpec $spec
     * @param Expr $expr
     * @param string $alias
     */
    public function __construct($spec, $expr, $alias)
    {
        $this->spec = $spec;
        $this->expr = $expr;
        $this->alias = $alias;
    }

    /**
     * @see Expr::buildExpr()
     */
    public function buildExpr()
    {
        return "{$this->expr->buildExpr()} AS {$this->spec->quoteIdentifier($this->alias)}";
    }
}
