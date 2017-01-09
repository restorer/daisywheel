<?php

namespace daisywheel\querybuilder\ast\expr;

use daisywheel\querybuilder\BuildException;
use daisywheel\querybuilder\ast\Expr;

class PlaceholderExpr implements Expr
{
    /** @var mixed */
    protected $name;

    /**
     * @param $name string
     */
    public function __construct($name)
    {
        if (!preg_match('/^:[_0-9A-Za-z]+$/', $name)) {
            throw new BuildException('Invalid placeholder name');
        }

        $this->name = $name;
    }

    /**
     * @implements Expr
     */
    public function build()
    {
        return $this->name;
    }
}
