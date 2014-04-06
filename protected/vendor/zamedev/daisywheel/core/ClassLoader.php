<?php

namespace daisywheel\core;

class ClassLoader
{
    protected $map = array();

    public function add($namespace, $path)
    {
        $this->map[$namespace . '\\'] = $path;
    }

    public function register($prepend=false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    public function loadClass($className)
    {
        $className = ltrim($className, '\\');

        foreach ($this->map as $namespace => $path) {
            $len = strlen($namespace);

            if (substr($className, 0, $len) == $namespace) {
                require_once $path
                    . DIRECTORY_SEPARATOR
                    . str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, substr($className, $len))
                    . '.php';

                break;
            }
        }
    }

    public static function create($namespace, $path)
    {
        $loader = new self();
        $loader->add($namespace, $path);
        $loader->register();

        return $loader;
    }
}
