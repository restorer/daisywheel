<?php

namespace daisywheel\db\builder;

use daisywheel\core\Entity;

class Command extends Entity
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
