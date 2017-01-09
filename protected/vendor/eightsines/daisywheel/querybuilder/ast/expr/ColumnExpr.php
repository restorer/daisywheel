<?php

namespace daisywheel\querybuilder\ast\expr;

use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\Expr;
use daisywheel\querybuilder\ast\Table;

class ColumnExpr implements Expr
{
    /** @var BuildSpec */
    protected $spec;

    /** @var string */
    protected $name;

    /** @var Table|null */
    protected $table;

    /**
     * @param $spec BuildSpec
     * @param $name string
     * @param $table Table|null
     */
    public function __construct($spec, $name, $table = null)
    {
        $this->spec = $spec;
        $this->name = $name;
        $this->table = $table;
    }

    /**
     * @implements Expr
     */
    public function build()
    {
        return ($this->table === null ? '' : ($this->table->build() . '.'))
            . ($this->name === '*' ? '*' : $this->spec->quoteIdentifier($this->name));
    }
}
