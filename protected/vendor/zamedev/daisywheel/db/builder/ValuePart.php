<?php

namespace daisywheel\db\builder;

use daisywheel\core\InvalidArgumentsException;

class ValuePart extends PartWithAlias
{
    protected $value = null;

    protected function __construct($arguments)
    {
        if (count($arguments) === 1) {
            $this->value = $arguments[0];
        } else {
            throw new InvalidArgumentsException();
        }
    }

    protected function getValue()
    {
        return $this->value;
    }

    public static function create($arguments)
    {
        if (count($arguments) === 1 && ($arguments[0] instanceof Part)) {
            return $arguments[0];
        } else {
            return new self($arguments);
        }
    }
}
