<?php

namespace daisywheel\db\builder;

use daisywheel\core\InvalidArgumentsException;

class FieldPart extends PartWithAlias
{
    protected $tableName = '';
    protected $fieldName = '';

    protected function __construct($arguments)
    {
        if (count($arguments) === 2) {
            $this->tableName = $arguments[0];
            $this->fieldName = $arguments[1];
        } elseif (count($arguments) === 1) {
            $this->fieldName = $arguments[0];
        } else {
            throw new InvalidArgumentsException();
        }
    }

    protected function getTableName()
    {
        return $this->tableName;
    }

    protected function getFieldName()
    {
        return $this->fieldName;
    }

    public static function create($arguments)
    {
        if (count($arguments) === 1 && ($arguments[0] instanceof FieldPart)) {
            return $arguments[0];
        } else {
            return new self($arguments);
        }
    }
}
