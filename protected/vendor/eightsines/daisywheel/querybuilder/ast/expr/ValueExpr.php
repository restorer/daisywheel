<?php

namespace daisywheel\querybuilder\ast\expr;

use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\BuildException;
use daisywheel\querybuilder\ast\Expr;

class ValueExpr implements Expr
{
    /** @var BuildSpec */
    protected $spec;

    /** @var mixed */
    protected $value;

    /**
     * @param $spec BuildSpec
     * @param $value mixed
     */
    public function __construct($spec, $value)
    {
        if ($value !== null && !is_bool($value) && !is_scalar($value)) {
            throw new BuildException('Unsupported value type "' . gettype($value) . '"');
        }

        $this->spec = $spec;
        $this->value = $value;
    }

    public function isNull()
    {
        return ($this->value === null);
    }

    /**
     * @implements Expr
     */
    public function build()
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