<?php

namespace daisywheel\core;

class Component extends Entity
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