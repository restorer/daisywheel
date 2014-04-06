<?php

namespace daisywheel\core;

class Context
{
    protected static $componentMap = array(
        'db' => 'daisywheel\\db\\Connection',
    );

    protected $config = null;
    protected $components = array();

    public function __construct($config)
    {
        mb_internal_encoding('UTF-8');
        $this->config = $config;
    }

    public function __get($name)
    {
        $method = "get{$name}";

        if (method_exists($this, $method)) {
            return $this->$method();
        } elseif (array_key_exists($name, $this->components)) {
            return $this->components[$name];
        } else {
            $this->components[$name] = $this->requireComponent($name);
            return $this->components[$name];
        }
    }

    protected function getConfig()
    {
        return $this->config;
    }

    protected function requireComponent($name)
    {
        $info = $this->config->get("components/{$name}");

        if (!$info) {
            throw new ComponentNotDefinedException("Component \"$name\" not defined");
        }

        $result = null;
        $componentConfig = $this->config->slice("components/{$name}");

        if (is_string($info)) {
            if (!is_subclass_of($info, 'Component')) {
                throw new ComponentNotDefinedException("Class \"$info\" for component \"$name\" must extend daisywheel\\core\\Component");
            }

            $result = new $info($this);
            $result->init($componentConfig);
            return $result;
        } elseif (is_array($info)) {
            if (isset($info['class'])) {
                $class = $info['class'];
            } elseif (isset(self::$componentMap[$name])) {
                $class = self::$componentMap[$name];
            } else {
                throw new ComponentNotDefinedException("Class not defined for component \"$name\"");
            }

            if (!is_subclass_of($class, 'daisywheel\\core\\Component')) {
                throw new ComponentNotDefinedException("Class \"$class\" for component \"$name\" must extend daisywheel\\core\\Component");
            }

            $result = new $class($this);
            $result->init($componentConfig->remove('class'));
            return $result;
        } elseif (is_object($info)) {
            if (!($info instanceof Component)) {
                throw new ComponentNotDefinedException("Component \"$name\" must extend daisywheel\\core\\Component");
            }

            return $info;
        }

        throw new ComponentNotDefinedException("Component \"$name\" is invalid");
    }
}
