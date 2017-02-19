<?php

namespace daisywheel\querybuilder\ast\commands\alter;

use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\parts\TablePart;
use daisywheel\querybuilder\BuildSpec;

class DropForeignKeyCommand implements Command
{
    /** @var BuildSpec */
    protected $spec;

    /** @var TablePart */
    protected $table;

    /** @var string */
    protected $name;

    /**
     * @param BuildSpec $spec
     * @param TablePart $table
     * @param string $name
     */
    public function __construct($spec, $table, $name)
    {
        $this->spec = $spec;
        $this->table = $table;
        $this->name = $name;
    }

    /**
     * @see Command::build()
     */
    public function build()
    {
        return [];
    }
}
