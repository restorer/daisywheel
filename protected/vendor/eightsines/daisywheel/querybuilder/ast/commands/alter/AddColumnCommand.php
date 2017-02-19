<?php

namespace daisywheel\querybuilder\ast\commands\alter;

use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\parts\DataTypePart;
use daisywheel\querybuilder\ast\parts\TablePart;
use daisywheel\querybuilder\BuildSpec;

class AddColumnCommand implements Command
{
    /** @var BuildSpec */
    protected $spec;

    /** @var TablePart */
    protected $table;

    /** @var DataTypePart */
    protected $column;

    /**
     * @param BuildSpec $spec
     * @param TablePart $table
     * @param DataTypePart $column
     */
    public function __construct($spec, $table, $column)
    {
        $this->spec = $spec;
        $this->table = $table;
        $this->column = $column;
    }

    /**
     * @see Command::build()
     */
    public function build()
    {
        return ["ALTER TABLE {$this->table->buildPart()} ADD {$this->column->buildPart()}"];
    }
}
