<?php

namespace daisywheel\querybuilder\ast\expr;

use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\Expr;
use daisywheel\querybuilder\ast\parts\TablePart;

class ColumnExpr implements Expr
{
    /** @var BuildSpec */
    protected $spec;

    /** @var string */
    protected $name;

    /** @var TablePart|null */
    protected $table;

    /**
     * @param BuildSpec $spec
     * @param string $name
     * @param TablePart|null $table
     */
    public function __construct($spec, $name, $table = null)
    {
        $this->spec = $spec;
        $this->name = $name;
        $this->table = $table;
    }

    /**
     * @see Expr::buildExpr()
     */
    public function buildExpr()
    {
        return ($this->table === null ? '' : ($this->table->buildPart() . '.'))
            . ($this->name === '*' ? '*' : $this->spec->quoteIdentifier($this->name));
    }
}
