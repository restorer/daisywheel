<?php

namespace daisywheel\db\builder;

use daisywheel\core\Object;
use daisywheel\core\InvalidArgumentsException;

class Table extends Object
{
    protected $temporary = false;
    protected $name = null;
    protected $asName = '';

    protected function __construct($temporary, $name, $asName)
    {
        $this->temporary = $temporary;
        $this->name = $name;
        $this->asName = $asName;
    }

    protected function getTemporary()
    {
        return $this->temporary;
    }

    protected function getName()
    {
        return $this->name;
    }

    protected function getAsName()
    {
        return $this->asName;
    }

    public static function createTemporary($name)
    {
        return new self(true, $name, '');
    }

    public static function create($arguments)
    {
        if ($arguments instanceof Table) {
            return $arguments;
        } elseif (!is_array($arguments)) {
            return new self(false, $arguments, '');
        } elseif (count($arguments) === 1) {
            if ($arguments[0] instanceof Table) {
                return $arguments[0];
            } else {
                return new self(false, $arguments[0], '');
            }
        } elseif (count($arguments) === 2) {
            if ($arguments[0] instanceof Table) {
                return new self($arguments[0]->temporary, $arguments[0]->name, $arguments[1]);
            } else {
                return new self(false, $arguments[0], $arguments[1]);
            }
        } else {
            throw new InvalidArgumentsException();
        }
    }
}
