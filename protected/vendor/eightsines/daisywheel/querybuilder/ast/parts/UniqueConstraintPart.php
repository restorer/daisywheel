<?php

namespace daisywheel\querybuilder\ast\parts;

use daisywheel\querybuilder\ast\Part;

class UniqueConstraintPart implements Part
{
    /** @var BuildSpec */
    protected $spec;

    /** @var TablePart */
    protected $table;

    /** @var string */
    protected $name;

    /** @var string[] */
    protected $columns;

    /**
     * @param BuildSpec $spec
     * @param TablePart $table
     * @param string $name
     * @param string[] $columns
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
        $this->columns = $columns;
    }

    /**
     * @see Part::buildPart()
     */
    public function buildPart()
    {
        return "CONSTRAINT {$this->spec->quoteConstraint($this->table->getName(), $this->name)} UNIQUE ("
            . join(', ', array_map(/** @return string */ function ($v) {
                return $this->spec->quoteIdentifier($v);
            }, $this->columns))
            . ')';
    }
}
