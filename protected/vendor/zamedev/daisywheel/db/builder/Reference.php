<?php

namespace daisywheel\db\builder;

use daisywheel\core\Object;
use daisywheel\core\InvalidArgumentsException;

class Reference extends Object
{
    const OPTION_RESTRICT = 'RESTRICT';
    const OPTION_CASCADE = 'CASCADE';
    const OPTION_SET_NULL = 'SET_NULL';

    protected $tableName = '';
    protected $fieldName = '';
    protected $onDelete = self::OPTION_RESTRICT;
    protected $onUpdate = self::OPTION_RESTRICT;

    public function __construct($arguments)
    {
        if (count($arguments) === 2) {
            $this->tableName = $arguments[0];
            $this->fieldName = $arguments[1];
        } else {
            throw new InvalidArgumentsException();
        }
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

    protected function getTableName()
    {
        return $this->tableName;
    }

    protected function getFieldName()
    {
        return $this->fieldName;
    }

    protected function getOnDelete()
    {
        return $this->onDelete;
    }

    protected function getOnUpdate()
    {
        return $this->onUpdate;
    }
}
