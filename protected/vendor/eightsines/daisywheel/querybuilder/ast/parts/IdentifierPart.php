<?php

namespace daisywheel\querybuilder\ast\parts;

use daisywheel\querybuilder\ast\Part;
use daisywheel\querybuilder\BuildSpec;

class IdentifierPart implements Part
{
    /** @var BuildSpec */
    protected $spec;

    /** @var string */
    protected $name;

    /**
     * @param BuildSpec $spec
     * @param string $name
     */
    public function __construct($spec, $name)
    {
        $this->spec = $spec;
        $this->name = $name;
    }

    /**
     * @see Part::buildPart()
     */
    public function buildPart()
    {
        return $this->spec->quoteIdentifier($this->name);
    }
}
