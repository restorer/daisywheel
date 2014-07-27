<?php

namespace daisywheel\db\builder;

class DropIndexCommand extends Command
{
    protected $indexName = '';
    protected $table = null;

    public function __construct($driver, $indexName)
    {
        parent::__construct($driver);
        $this->indexName = $indexName;
    }

    public function on($table)
    {
        $this->table = Table::create($table);
        return $this;
    }

    public function getIndexName()
    {
        return $this->indexName;
    }

    public function getTable()
    {
        return $this->table;
    }
}
