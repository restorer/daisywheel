<?php

namespace daisywheel\db\builder;

use daisywheel\core\Entity;
use daisywheel\core\UnknownMethodException;

class PartWithAlias extends Entity implements Part
{
    protected $asName = '';

    protected function magicAs($asName)
    {
        $this->asName = $asName;
        return $this;
    }

    protected function getAsName()
    {
        return $this->asName;
    }

    public function __call($name, $arguments)
    {
        $method = "magic{$name}";

        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arguments);
        }

        throw new UnknownMethodException('Calling unknown method ' . get_class($this) . "::{$method}");
    }
}
