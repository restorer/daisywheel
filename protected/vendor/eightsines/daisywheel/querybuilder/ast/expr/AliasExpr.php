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
     * @param $spec BuildSpec
     * @param $expr Expr
     * @param $alias string
     */
    public function __construct($spec, $expr, $alias)
    {
        $this->spec = $spec;
        $this->expr = $expr;
        $this->alias = $alias;
    }

    /**
     * @implements Expr
     */
    public function build()
    {
        return "{$this->expr->build()} AS {$this->spec->quoteIdentifier($this->alias)}";
    }
}
