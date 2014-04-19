<?php

namespace daisywheel\db\builder;

class CommandWithAlias extends Command
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
