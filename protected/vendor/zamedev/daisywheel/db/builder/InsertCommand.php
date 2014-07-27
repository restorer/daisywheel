<?php

namespace daisywheel\db\builder;

use daisywheel\core\InvalidArgumentsException;

class InsertCommand extends Command
{
    protected $into = null;
    protected $columns = array();
    protected $values = array();
    protected $select = null;

    public function into()
    {
        $this->into = Table::create(func_get_args());
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

        $this->columns = array_map(function($v) {
            return ColumnPart::create(array($v));
        }, $arguments);

        return $this;
    }

    public function values()
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

        foreach ($arguments as $list) {
            if (!is_array($list)) {
                throw new InvalidArgumentsException();
            }

            $this->values[] = array_map(function($v) {
                return ValuePart::create(array($v));
            }, $list);
        }

        return $this;
    }

    public function select()
    {
        $this->select = new SelectCommand($this->driver, $this);
        return $this->select;
    }

    protected function getInto()
    {
        return $this->into;
    }

    protected function getColumns()
    {
        return $this->columns;
    }

    protected function getValues()
    {
        return $this->values;
    }

    protected function getSelect()
    {
        return $this->select;
    }
}
