<?php

namespace daisywheel\querybuilder\ast\commands;

use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\Table;

class DropTableCommand implements Command
{
    /** @var BuildSpec */
    protected $spec;

    /** @var Table */
    protected $table;

    /**
     * @param $spec BuildSpec
     * @param $table Table
     */
    public function __construct($spec, $table)
    {
        $this->spec = $spec;
        $this->table = $table;
    }

    /**
     * @implements Expr
     */
    public function build()
    {
        return $this->spec->buildDropTableCommand($this->table->getName(), $this->table->getTemporary());
    }

    /**
     * @param $tableSql string
     * @return string
     */
    public static function basicBuild($tableSql)
    {
        return ["DROP {$tableSql}"];
    }
}
