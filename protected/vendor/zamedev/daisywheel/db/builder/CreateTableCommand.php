<?php

namespace daisywheel\db\builder;

use daisywheel\core\InvalidArgumentsException;

class CreateTableCommand extends Command
{
    protected $tableName = null;
    protected $temporary = false;
    protected $columns = array();
    protected $uniqueList = array();
    protected $indexList = array();
    protected $foreignKeyList = array();

    public function __construct($driver, $tableName, $temporary)
    {
        parent::__construct($driver);

        $this->tableName = $tableName;
        $this->temporary = $temporary;
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
            return ColumnPart::create(array($v));
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
            $arguments = array($arguments);
        }

        foreach ($arguments as $argument) {
            if (count($argument) < 2) {
                throw new InvalidArgumentsException();
            }

            $this->uniqueList[] = array(
                'name' => $argument[0],
                'columns' => (is_array($argument[1]) ? $argument[1] : array_slice($argument, 1)),
            );
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
            $arguments = array($arguments);
        }

        foreach ($arguments as $argument) {
            if (count($argument) < 2) {
                throw new InvalidArgumentsException();
            }

            $this->indexList[] = array(
                'name' => $argument[0],
                'columns' => (is_array($argument[1]) ? $argument[1] : array_slice($argument, 1)),
            );
        }

        return $this;
    }

    public function foreignKey()
    {
        $reference = new ForeignReference($this, func_get_args());
        $this->foreignKeyList[] = $reference;
        return $reference;
    }

    protected function getTemporary()
    {
        return $this->temporary;
    }

    protected function getTableName()
    {
        return $this->tableName;
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
