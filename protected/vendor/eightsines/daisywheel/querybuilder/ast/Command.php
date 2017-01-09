<?php

namespace daisywheel\querybuilder\ast;

interface Command
{
    /**
     * @return string[]
     */
    public function build();
}
