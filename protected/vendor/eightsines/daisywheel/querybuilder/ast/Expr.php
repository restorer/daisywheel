<?php

namespace daisywheel\querybuilder\ast;

interface Expr
{
    /**
     * @return string
     */
    public function build();
}
