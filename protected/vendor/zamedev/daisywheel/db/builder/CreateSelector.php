<?php

namespace daisywheel\db\builder;

class CreateSelector
{
    protected $driver = null;

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function table()
    {
        return new CreateTableCommand($this->driver, new Table(func_get_args()), false);
    }

    public function temporaryTable()
    {
        return new CreateTableCommand($this->driver, new Table(func_get_args()), true);
    }
}
