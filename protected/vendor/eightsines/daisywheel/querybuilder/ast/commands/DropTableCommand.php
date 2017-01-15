<?php

namespace daisywheel\querybuilder\ast\commands;

use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\parts\TablePart;

class DropTableCommand implements Command
{
    /** @var BuildSpec */
    protected $spec;

    /** @var TablePart */
    protected $table;

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
     * @see Command::build()
     */
    public function build()
    {
        return $this->spec->buildDropTableCommand($this->table->buildPart(), $this->table->getTemporary());
    }

    /**
     * @param string $tableSql
     * @param string $prependSql
     * @return string[]
     */
    public static function basicBuild($tableSql, $prependSql)
    {
        return ["DROP {$prependSql}TABLE {$tableSql}"];
    }
}
