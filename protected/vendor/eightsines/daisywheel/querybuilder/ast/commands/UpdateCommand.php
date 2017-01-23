<?php

namespace daisywheel\querybuilder\ast\commands;

use daisywheel\querybuilder\BuildException;
use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\Expr;
use daisywheel\querybuilder\ast\parts\SetPart;
use daisywheel\querybuilder\ast\parts\TablePart;

class UpdateCommand implements Command
{
    /** @var BuildSpec */
    protected $spec;

    /** @var TablePart */
    protected $table;

    /** @var SetPart[] */
    protected $setList = [];

    /** @var Expr|null */
    protected $where = null;

    /**
     * @param BuildSpec $spec
     * @param TablePart $table
     */
    public function __construct($spec, $table)
    {
        $this->spec = $spec;
        $this->table = $table;
    }

    /**
     * @param string|mixed[] $columnOrList
     * @param Expr|null $expr
     * @throws BuildException
     * @return self
     */
    public function set($columnOrList, $expr = null)
    {
        if (!is_array($columnOrList)) {
            if ($expr === null) {
                throw new BuildException('Expression is required for non-array first argument');
            }

            $columnOrList = [[$columnOrList, $expr]];
        }

        foreach ($columnOrList as $item) {
            if (!is_array($item) || count($item) !== 2) {
                throw new BuildException('Each item must be an array and have exactly two elements');
            }

            $this->setList[] = new SetPart($this->spec, $item[0], $item[1]);
        }

        return $this;
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
        if (empty($this->setList)) {
            throw new BuildException('At least one "set" part is required');
        }

        return "UPDATE {$this->table->buildPart()} SET " . join(', ', array_map(function ($v) {
            return $v->buildPart();
        }, $this->setList)) . ($this->where === null ? '' : " WHERE {$this->where->buildExpr()}");
    }
}
