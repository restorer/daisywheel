<?php

namespace daisywheel\db\builder;

use daisywheel\core\InvalidArgumentsException;

class CreateIndexCommand extends Command
{
    protected $indexName = '';
    protected $table = null;
    protected $columnNames = array();

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

    public function columns()
    {
        $arguments = func_get_args();

        if (count($arguments) && is_array($arguments[0])) {
            if (count($arguments) > 1) {
                throw new InvalidArgumentsException();
            }

            $arguments = $arguments[0];
        }

        $this->columnNames = $arguments;
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

    public function getColumnNames()
    {
        return $this->columnNames;
    }
}
