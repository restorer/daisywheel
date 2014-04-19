<?php

namespace daisywheel\db\builder;

use daisywheel\core\Object;
use daisywheel\core\UnknownMethodException;

class PartWithAlias extends Object implements Part
{
    protected $asName = '';

    protected function magicAs($asName)
    {
        $this->asName = $asName;
        return $this;
    }

    protected function getAsName()
    {
        return $this->asName;
    }
}
