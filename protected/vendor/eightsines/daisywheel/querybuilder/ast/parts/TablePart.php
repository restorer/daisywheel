<?php

namespace daisywheel\querybuilder\ast\parts;

use daisywheel\querybuilder\ast\Part;
use daisywheel\querybuilder\BuildSpec;

class TablePart implements Part
{
    /** @var BuildSpec */
    protected $spec;

    /** @var string */
    protected $name;

    /** @var boolean */
    protected $temporary;

    /**
     * @param BuildSpec $spec
     * @param string $name
     * @param boolean $temporary
     */
    public function __construct($spec, $name, $temporary)
    {
        $this->spec = $spec;
        $this->name = $name;
        $this->temporary = $temporary;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function getTemporary()
    {
        return $this->temporary;
    }

    /**
     * @see Part::buildPart()
     */
    public function buildPart()
    {
        return $this->spec->quoteTable($this->name, $this->temporary);
    }

    /**
     * @param BuildSpec $spec
     * @param string|TablePart $table
     *
     * @return TablePart
     */
    public static function create($spec, $table)
    {
        return (($table instanceof self) ? $table : new self($spec, $table, false));
    }
}
