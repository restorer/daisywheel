<?php

namespace daisywheel\web;

use daisywheel\core\Component;

class Session extends Component implements Countable, IteratorAggregate, ArrayAccess
{
    public function init($config)
    {
        if (session_id() == '') {
            session_start();
        }
    }

    public function has($key)
    {
        return array_key_exists($key, $_SESSION);
    }

    public function get($key, $def=null)
    {
        return (array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $def);
    }

    public function set($key, $value)
    {
        if ($key === null) {
            $_SESSION[] = $value;
        } else {
            $_SESSION[$key] = $value;
        }

        return $this;
    }

    public function unset($key)
    {
        unset($_SESSION[$key]);
        return $this;
    }

    // Countable
    public function count()
    {
        return count($_SESSION);
    }

    // IteratorAggregate
    public function getIterator()
    {
        return new \ArrayIterator($_SESSION);
    }

    // ArrayAccess
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $_SESSION);
    }

    // ArrayAccess
    public function offsetGet($offset)
    {
        return (array_key_exists($key, $_SESSION) ? $_SESSION[$key] : null);
    }

    // ArrayAccess
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $_SESSION[] = $value;
        } else {
            $_SESSION[$offset] = $value;
        }
    }

    // ArrayAccess
    public function offsetUnset($offset)
    {
        unset($_SESSION[$offset]);
    }
}
