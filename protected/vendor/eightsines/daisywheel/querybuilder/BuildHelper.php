<?php

namespace daisywheel\querybuilder;

use daisywheel\querybuilder\ast\Command;

class BuildHelper
{
    /**
     * @param mixed|mixed[] $arg
     * @return mixed[]
     */
    public static function arg($arg)
    {
        return is_array($arg) ? $arg : [$arg];
    }

    /**
     * @param mixed[] $args
     * @throws BuildException
     * @return mixed[]
     */
    public static function args($args)
    {
        if (!empty($args) && is_array($args[0])) {
            if (count($args) !== 1) {
                throw new BuildException('If first argumets is array, than exactly one argument required');
            }

            return $args[0];
        }

        return $args;
    }
}
