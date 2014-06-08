<?php

namespace daisywheel\core;

class Object
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

    public function __call($name, $arguments)
    {
        $method = "magic{$name}";

        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arguments);
        }

        return $this->unknownMethodCalled($name, $arguments);
    }

    protected function unknownMethodCalled($name, $arguments) {
        throw new UnknownMethodException('Calling unknown method ' . get_class($this) . "::{$name}");
    }
}
