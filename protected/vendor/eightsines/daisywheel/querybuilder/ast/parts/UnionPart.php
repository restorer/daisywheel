<?php

namespace daisywheel\querybuilder\ast\parts;

use daisywheel\querybuilder\ast\Part;
use daisywheel\querybuilder\ast\commands\SelectCommand;

class UnionPart implements Part
{
    /** @var SelectCommand */
    protected $command;

    /** @var boolean */
    protected $all;

    /**
     * @param SelectCommand $command
     * @param boolean $all
     */
    public function __construct($command, $all)
    {
        $this->command = $command;
        $this->all = $all;
    }

    /**
     * @see Part::buildPart()
     */
    public function buildPart()
    {
        return 'UNION ' . ($this->all ? 'ALL ' : '') . $this->command->buildSql();
    }
}
