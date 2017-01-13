<?php

namespace daisywheel\querybuilder\ast\commands;

use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\parts\TablePart;

class TruncateTableCommand implements Command
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
        return $this->spec->buildTruncateTableCommand($this->table->buildPart(), $this->table->getName());
    }

    /**
     * @param string $truncateSql
     * @return string
     */
    public static function basicBuild($truncateSql)
    {
        return ["TRUNCATE TABLE {$truncateSql}"];
    }
}
