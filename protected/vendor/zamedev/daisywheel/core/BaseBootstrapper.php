<?php

namespace daisywheel\core;

abstract class BaseBootstrapper extends Component
{
    public function bootstrap()
    {
    }

    abstract public function run();
}
