<?php

spl_autoload_register(function ($className) {
    if (preg_match('/^daisywheel\\\\/', $className)) {
        require __DIR__
            . DIRECTORY_SEPARATOR
            . '..'
            . DIRECTORY_SEPARATOR
            . str_replace(['\\', '_'], DIRECTORY_SEPARATOR, substr($className, strlen('daisywheel\\')))
            . '.php';
    }
}, true, false);
