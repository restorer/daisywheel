<?php

namespace daisywheel\core;

// Currently, context implements service locator pattern.
// While it may be considered as anti-pattern, we think that it simplifies rapid development.

/**
 * @property daisywheel\core\Config $config
 * @property daisywheel\core\BaseBootstrapper $bootstrapper
 * @property daisywheel\core\Component\Response $response
 */
class Context
{
    const CHARSET = 'UTF-8';

    protected static $componentMap = [
        'db' => 'daisywheel\\db\\Connection',
    ];

    protected $components = [];

    public function __construct($config)
    {
        mb_internal_encoding(self::CHARSET);
        $this->components['config'] = $config;
    }

    public function hasComponent($name)
    {
        return array_key_exists($name, $this->components);
    }

    public function getComponent($name)
    {
        return (array_key_exists($name, $this->components) ? $this->components[$name] : null);
    }

    public function setComponent($name, $component)
    {
        if (!($component instanceof Component)) {
            throw new ComponentNotDefinedException("Component \"$name\" must extend daisywheel\\core\\Component");
        }

        $this->components[$name] = $component;
        return $this;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->components)) {
            return $this->components[$name];
        } else {
            $this->components[$name] = $this->requireComponent($name);
            return $this->components[$name];
        }
    }

    public function __isset($name)
    {
        return (array_key_exists($name, $this->components) || $this->config->get("components/{$name}"));
    }

    protected function requireComponent($name)
    {
        $info = $this->config->get("components/{$name}");

        if (!$info) {
            throw new ComponentNotDefinedException("Component \"$name\" not defined");
        }

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

            // TODO: https://bugs.php.net/bug.php?id=53727, consider rewrite using ReflectionClass::implementsInterface
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
