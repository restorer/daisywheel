<?php

namespace daisywheel\querybuilder;

class BuildHelper
{
    public static function args($args)
    {
        if (!empty($args) && is_array($args[0])) {
            if (count($args) !== 1) {
                throw BuildException('If first argumets is array, than exactly one argument required');
            }

            return $args[0];
        }

        return $args;
    }
}
