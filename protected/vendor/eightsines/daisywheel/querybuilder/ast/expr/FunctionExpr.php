<?php

namespace daisywheel\querybuilder\ast\expr;

use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\BuildException;
use daisywheel\querybuilder\ast\Expr;

class FunctionExpr implements Expr
{
    const TYPE_AVG = 'AVG';
    const TYPE_COUNT = 'COUNT';
    const TYPE_MAX = 'MAX';
    const TYPE_MIN = 'MIN';
    const TYPE_SUM = 'SUM';
    const TYPE_COALESCE = 'COALESCE';
    const TYPE_ABS = 'ABS';
    const TYPE_ROUND = 'ROUND';
    const TYPE_CONCAT = 'CONCAT';
    const TYPE_LENGTH = 'LENGTH';
    const TYPE_LOWER = 'LOWER';
    const TYPE_LTRIM = 'LTRIM';
    const TYPE_RTRIM = 'RTRIM';
    const TYPE_SUBSTR = 'SUBSTR';
    const TYPE_TRIM = 'TRIM';
    const TYPE_UPPER = 'UPPER';

    /** @var BuildSpec */
    protected $spec;

    /** @var string */
    protected $type;

    /** @var Expr[] */
    protected $operands;

    /**
     * @param BuildSpec $spec
     * @param string $type
     * @param Expr[] $operands
     * @throws BuildException
     */
    public function __construct($spec, $type, $operands)
    {
        if (empty($operands)) {
            throw new BuildException("At least one operand required");
        }

        $this->spec = $spec;
        $this->type = $type;
        $this->operands = $operands;
    }

    /**
     * @see Expr::buildExpr()
     */
    public function buildExpr()
    {
        return $this->spec->buildFunctionExpr($this->type, $this->operands);
    }

    /**
     * @param string $type
     * @param Expr[] $operands
     * @return string
     */
    public static function basicBuildExpr($type, $operands)
    {
        return "{$type}(" . join(', ', array_map(function ($v) {
            return $v->buildExpr();
        }, $operands)) . ')';
    }
}
