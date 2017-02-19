<?php

namespace daisywheel\querybuilder\ast\parts;

use daisywheel\querybuilder\ast\Part;
use daisywheel\querybuilder\BuildSpec;

class TableAliasPart implements Part
{
    /** @var BuildSpec */
    protected $spec;

    /** @var TablePart */
    protected $table;

    /** @var string|null */
    protected $alias;

    /**
     * @param BuildSpec $spec
     * @param TablePart $table
     * @param string|null $alias
     */
    public function __construct($spec, $table, $alias)
    {
        $this->spec = $spec;
        $this->table = $table;
        $this->alias = $alias;
    }

    /**
     * @see Part::buildPart()
     */
    public function buildPart()
    {
        return $this->table->buildPart()
            . ($this->alias === null ? '' : " AS {$this->spec->quoteIdentifier($this->alias)}");
    }
}
