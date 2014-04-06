<?php

namespace daisywheel\db\builder;

use daisywheel\core\InvalidArgumentsException;

class UpdateCommand extends Command
{
    protected $table = '';
    protected $setList = array();
    protected $where = null;

    public function table()
    {
        $this->table = new Table(func_get_args());
        return $this;
    }

    public function set()
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

        foreach ($arguments as $item) {
            if (!is_array($item) || count($item) !== 2) {
                throw new InvalidArgumentsException();
            }

            $this->setList[] = array(
                'field' => FieldPart::create(array($item[0])),
                'value' => ValuePart::create(array($item[1])),
            );
        }

        return $this;
    }

    public function where()
    {
        $this->where = ExpressionPart::create(func_get_args());
        return $this;
    }

    protected function getTable()
    {
        return $this->table;
    }

    protected function getSetList()
    {
        return $this->setList;
    }

    protected function getWhere()
    {
        return $this->where;
    }
}