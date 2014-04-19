<?php

namespace daisywheel\db\builder;

use daisywheel\core\Object;

class Command extends Object
{
    protected $driver = null;

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function build()
    {
        return $this->driver->build($this);
    }
}
