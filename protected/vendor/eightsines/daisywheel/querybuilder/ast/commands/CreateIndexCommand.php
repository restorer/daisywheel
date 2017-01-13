<?php

namespace daisywheel\querybuilder\ast\commands;

use daisywheel\querybuilder\BuildException;
use daisywheel\querybuilder\BuildHelper;
use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\parts\TablePart;

class CreateIndexCommand implements Command
{
    /** @var BuildSpec */
    protected $spec;

    /** @var string */
    protected $name;

    /** @var TablePart */
    protected $table;

    /** @var string[] */
    protected $columns;

    /**
     * @param BuildSpec $spec
     * @param TablePart $table
     * @param string $name
     * @param string|string[] $columns
     * @throws BuildException
     */
    public function __construct($spec, $table, $name, $columns)
    {
        if (empty($columns)) {
            throw new BuildException('At least one column required');
        }

        $this->spec = $spec;
        $this->table = $table;
        $this->name = $name;
        $this->columns = BuildHelper::arg($columns);
    }

    /**
     * @see Command::build()
     */
    public function build()
    {
        return [
            'CREATE INDEX '
            . $this->spec->quoteConstraint($this->table->getName(), $this->name)
            . ' ON '
            . $this->table->buildPart()
            . ' ('
            . join(', ', array_map(function ($v) {
                return $this->spec->quoteIdentifier($v);
            }, $this->columns))
            . ')'
        ];
    }
}
