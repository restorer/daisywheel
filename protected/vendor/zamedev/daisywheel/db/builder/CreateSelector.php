<?php

namespace daisywheel\db\builder;

class CreateSelector
{
    protected $driver = null;

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function table($tableName)
    {
        return new CreateTableCommand($this->driver, $tableName, false);
    }

    public function temporaryTable($tableName)
    {
        return new CreateTableCommand($this->driver, $tableName, true);
    }

    // public function index()
    // {
    // }
}
