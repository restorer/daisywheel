<?php

namespace daisywheel\core;

class Entity
{
    public function __get($name)
    {
        $method = "get{$name}";

        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            throw new UnknownPropertyException('Getting unknown property ' . get_class($this) . "::{$name}");
        }
    }

    public function __set($name, $value)
    {
        $method = "set{$name}";

        if (method_exists($this, $method)) {
            return $this->$method($value);
        } else {
            throw new UnknownPropertyException('Setting unknown property ' . get_class($this) . "::{$name}");
        }
    }
}
