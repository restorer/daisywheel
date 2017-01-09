<?php

namespace daisywheel\db\builder;

use daisywheel\core\Object;
use daisywheel\core\InvalidArgumentsException;

class ForeignReference extends Object
{
    const OPTION_RESTRICT = 'RESTRICT';
    const OPTION_CASCADE = 'CASCADE';
    const OPTION_SET_NULL = 'SET_NULL';

    protected $owner = null;
    protected $constraintName = '';
    protected $columns = [];
    protected $refTable = null;
    protected $refColumns = [];
    protected $onDelete = self::OPTION_RESTRICT;
    protected $onUpdate = self::OPTION_RESTRICT;

    public function __construct($owner, $arguments)
    {
        if (count($arguments) < 2) {
            throw new InvalidArgumentsException();
        }

        $this->owner = $owner;
        $this->constraintName = $arguments[0];
        $this->columns = (is_array($arguments[1]) ? $arguments[1] : array_slice($arguments, 1));
    }

    public function references() {
        $arguments = func_get_args();

        if (count($arguments) < 2) {
            throw new InvalidArgumentsException();
        }

        $this->refTable = Table::create($arguments[0]);
        $this->refColumns = (is_array($arguments[1]) ? $arguments[1] : array_slice($arguments, 1));

        return $this;
    }

    public function onDeleteRestrict()
    {
        $this->onDelete = self::OPTION_RESTRICT;
        return $this;
    }

    public function onDeleteCascade()
    {
        $this->onDelete = self::OPTION_CASCADE;
        return $this;
    }

    public function onDeleteSetNull()
    {
        $this->onDelete = self::OPTION_SET_NULL;
        return $this;
    }

    public function onUpdateRestrict()
    {
        $this->onUpdate = self::OPTION_RESTRICT;
        return $this;
    }

    public function onUpdateCascade()
    {
        $this->onUpdate = self::OPTION_CASCADE;
        return $this;
    }

    public function onUpdateSetNull()
    {
        $this->onUpdate = self::OPTION_SET_NULL;
        return $this;
    }

    protected function getConstraintName()
    {
        return $this->constraintName;
    }

    protected function getColumns()
    {
        return $this->columns;
    }

    protected function getRefTable()
    {
        return $this->refTable;
    }

    protected function getRefColumns()
    {
        return $this->refColumns;
    }

    protected function getOnDelete()
    {
        return $this->onDelete;
    }

    protected function getOnUpdate()
    {
        return $this->onUpdate;
    }

    protected function unknownMethodCalled($name, $arguments) {
        return call_user_func_array(array($this->owner, $name), $arguments);
    }
}
