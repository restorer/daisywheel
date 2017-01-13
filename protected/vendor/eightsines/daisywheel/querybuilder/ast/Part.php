<?php

namespace daisywheel\querybuilder\ast;

interface Part
{
    /**
     * @return string
     */
    public function buildPart();
}
