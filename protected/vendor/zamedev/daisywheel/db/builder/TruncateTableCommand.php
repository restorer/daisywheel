<?php

namespace daisywheel\db\builder;

class TruncateTableCommand extends Command
{
    protected $table = null;

    public function __construct($driver, $table)
    {
        parent::__construct($driver);
        $this->table = Table::create($table);
    }

    public function getTable()
    {
        return $this->table;
    }
}
