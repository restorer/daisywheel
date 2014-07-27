<?php

namespace daisywheel\db\builder;

class DropTableCommand extends Command
{
    protected $table = null;

    public function __construct($driver, $table)
    {
        parent::__construct($driver);
        $this->table = Table::create($table);
    }

    protected function getTable()
    {
        return $this->table;
    }
}
