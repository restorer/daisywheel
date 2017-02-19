<?php

spl_autoload_register(/** @return void */ function ($className) {
    if (preg_match('/^daisywheel\\\\/', $className)) {
        /** @noinspection PhpIncludeInspection */
        require __DIR__
            . DIRECTORY_SEPARATOR
            . '..'
            . DIRECTORY_SEPARATOR
            . str_replace(['\\', '_'], DIRECTORY_SEPARATOR, substr($className, strlen('daisywheel\\')))
            . '.php';
    }
}, true, false);
