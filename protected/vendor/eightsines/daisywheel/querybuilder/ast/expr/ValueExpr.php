<?php

namespace daisywheel\querybuilder\ast\expr;

use daisywheel\querybuilder\ast\Expr;
use daisywheel\querybuilder\BuildException;
use daisywheel\querybuilder\BuildSpec;

class ValueExpr implements Expr
{
    /** @var BuildSpec */
    protected $spec;

    /** @var mixed */
    protected $value;

    /**
     * @param BuildSpec $spec
     * @param mixed $value
     *
     * @throws BuildException
     */
    public function __construct($spec, $value)
    {
        if ($value !== null && !is_bool($value) && !is_scalar($value)) {
            throw new BuildException('Unsupported value type "' . gettype($value) . '"');
        }

        $this->spec = $spec;
        $this->value = $value;
    }

    /**
     * @return boolean
     */
    public function isNull()
    {
        return ($this->value === null);
    }

    /**
     * @see Expr::buildExpr()
     */
    public function buildExpr()
    {
        if ($this->value === null) {
            return 'NULL';
        } elseif (is_bool($this->value)) {
            return ($this->value ? '1' : '0');
        } else { // is_scalar($value)
            return $this->spec->quote($this->value);
        }
    }
}
