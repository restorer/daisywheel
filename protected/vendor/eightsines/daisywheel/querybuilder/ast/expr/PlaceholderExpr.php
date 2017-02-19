<?php

namespace daisywheel\querybuilder\ast\expr;

use daisywheel\querybuilder\ast\Expr;
use daisywheel\querybuilder\BuildException;

class PlaceholderExpr implements Expr
{
    /** @var mixed */
    protected $name;

    /**
     * @param string $name
     *
     * @throws BuildException
     */
    public function __construct($name)
    {
        if (!preg_match('/^:[_0-9A-Za-z]+$/', $name)) {
            throw new BuildException('Invalid placeholder name');
        }

        $this->name = $name;
    }

    /**
     * @see Expr::buildExpr()
     */
    public function buildExpr()
    {
        return $this->name;
    }
}
