<?php

namespace daisywheel\querybuilder\ast;

use daisywheel\querybuilder\BuildSpec;

class Table
{
    /** @var BuildSpec */
    protected $spec;

    /** @var string */
    protected $name;

    /** @var boolean */
    protected $temporary;

    /**
     * @param $spec BuildSpec
     * @param $name string
     * @param $temporary boolean
     */
    protected function __construct($spec, $name, $temporary)
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
     * @return string
     */
    public function getTemporary()
    {
        return $this->temporary;
    }

    /**
     * @return string
     */
    public function build()
    {
        return $this->spec->quoteTable($this->name, $this->temporary);
    }

    /**
     * @param $nameOrTable string|Table
     * @param $temporary boolean
     */
    public static function create($spec, $nameOrTable, $temporary = false)
    {
        return (($nameOrTable instanceof Table) ? $nameOrTable : new self($spec, $nameOrTable, $temporary));
    }
}
