<?php

namespace daisywheel\db\builder;

use daisywheel\core\Entity;
use daisywheel\core\InvalidArgumentsException;

class Table extends Entity
{
    protected $name = null;
    protected $asName = '';

    public function __construct($arguments)
    {
        if (count($arguments) === 2) {
            $this->name = $arguments[0];
            $this->asName = $arguments[1];
        } elseif (count($arguments) === 1) {
            $this->name = $arguments[0];
        } else {
            throw new InvalidArgumentsException();
        }
    }

    protected function getName()
    {
        return $this->name;
    }

    protected function getAsName()
    {
        return $this->asName;
    }
}
