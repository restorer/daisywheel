<?php

namespace daisywheel\core;

class Component extends Object
{
    protected $context = null;

    public function __construct($context)
    {
        $this->context = $context;
    }

    public function init($config)
    {
    }
}
