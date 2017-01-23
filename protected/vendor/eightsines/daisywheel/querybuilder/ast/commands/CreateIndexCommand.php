<?php

namespace daisywheel\querybuilder\ast\commands;

use daisywheel\querybuilder\BuildException;
use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\parts\TablePart;

class CreateIndexCommand implements Command
{
    /** @var BuildSpec */
    protected $spec;

    /** @var TablePart */
    protected $table;

    /** @var string */
    protected $name;

    /** @var string[] */
    protected $columns;

    /** @var boolean */
    protected $unique;

    /**
     * @param BuildSpec $spec
     * @param TablePart $table
     * @param string $name
     * @param string[] $columns
     * @param boolean $unique
     * @throws BuildException
     */
    public function __construct($spec, $table, $name, $columns, $unique)
    {
        if (empty($columns)) {
            throw new BuildException('At least one column required');
        }

        $this->spec = $spec;
        $this->table = $table;
        $this->name = $name;
        $this->columns = $columns;
        $this->unique = $unique;
    }

    /**
     * @return string
     */
    public function buildSql()
    {
        return 'CREATE '
            . ($this->unique ? 'UNIQUE ' : '')
            . 'INDEX '
            . $this->spec->quoteConstraint($this->table->getName(), $this->name)
            . ' ON '
            . $this->table->buildPart()
            . ' ('
            . join(', ', array_map(/** @return string */ function ($v) {
                return $this->spec->quoteIdentifier($v);
            }, $this->columns))
            . ')';
    }

    /**
     * @see Command::build()
     */
    public function build()
    {
        return [$this->buildSql()];
    }
}
