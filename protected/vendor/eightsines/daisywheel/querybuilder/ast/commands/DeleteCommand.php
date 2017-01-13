<?php

namespace daisywheel\querybuilder\ast\commands;

use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\Expr;
use daisywheel\querybuilder\ast\parts\TablePart;

class DeleteCommand implements Command
{
    /** @var TablePart */
    protected $table;

    /** @var Expr|null */
    protected $where = null;

    /**
     * @param TablePart $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * @param Expr $expr
     * @return self
     */
    public function where($expr)
    {
        $this->where = $expr;
        return $this;
    }

    /**
     * @see Command::build()
     */
    public function build()
    {
        return "DELETE FROM {$this->table->buildPart()}"
            . ($this->where === null ? '' : " WHERE {$this->where->buildExpr()}");
    }
}
