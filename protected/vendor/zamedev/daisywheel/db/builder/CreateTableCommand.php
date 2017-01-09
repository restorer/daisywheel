<?php

namespace daisywheel\db\builder;

use daisywheel\core\InvalidArgumentsException;

class CreateTableCommand extends Command
{
    protected $table = null;
    protected $columns = [];
    protected $uniqueList = [];
    protected $indexList = [];
    protected $foreignKeyList = [];

    public function __construct($driver, $table)
    {
        parent::__construct($driver);
        $this->table = Table::create($table);
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

        $this->columns = array_map(function($v) {
            return ColumnPart::create([$v]);
        }, $arguments);

        return $this;
    }

    public function unique()
    {
        $arguments = func_get_args();

        if (count($arguments) && is_array($arguments[0])) {
            if (count($arguments) > 1) {
                throw new InvalidArgumentsException();
            }

            $arguments = $arguments[0];
        } else {
            $arguments = [$arguments];
        }

        foreach ($arguments as $argument) {
            if (count($argument) < 2) {
                throw new InvalidArgumentsException();
            }

            $this->uniqueList[] = [
                'name' => $argument[0],
                'columns' => (is_array($argument[1]) ? $argument[1] : array_slice($argument, 1)),
            ];
        }

        return $this;
    }

    public function index()
    {
        $arguments = func_get_args();

        if (count($arguments) && is_array($arguments[0])) {
            if (count($arguments) > 1) {
                throw new InvalidArgumentsException();
            }

            $arguments = $arguments[0];
        } else {
            $arguments = [$arguments];
        }

        foreach ($arguments as $argument) {
            if (count($argument) < 2) {
                throw new InvalidArgumentsException();
            }

            $this->indexList[] = [
                'name' => $argument[0],
                'columns' => (is_array($argument[1]) ? $argument[1] : array_slice($argument, 1)),
            ];
        }

        return $this;
    }

    public function foreignKey()
    {
        $reference = new ForeignReference($this, func_get_args());
        $this->foreignKeyList[] = $reference;
        return $reference;
    }

    protected function getTable()
    {
        return $this->table;
    }

    protected function getColumns()
    {
        return $this->columns;
    }

    protected function getUniqueList()
    {
        return $this->uniqueList;
    }

    protected function getIndexList()
    {
        return $this->indexList;
    }

    protected function getForeignKeyList()
    {
        return $this->foreignKeyList;
    }
}
