<?php

namespace daisywheel\db\builder;

use daisywheel\core\InvalidArgumentsException;

class FunctionPart extends PartWithAlias
{
    const TYPE_AVG = 'AVG';
    const TYPE_COUNT = 'COUNT';
    const TYPE_MAX = 'MAX';
    const TYPE_MIN = 'MIN';
    const TYPE_SUM = 'SUM';
    const TYPE_COALESCE = 'COALESCE';
    const TYPE_ABS = 'ABS';
    const TYPE_ROUND = 'ROUND';
    const TYPE_CONCAT = 'CONCAT';
    const TYPE_LENGTH = 'LENGTH';
    const TYPE_LOWER = 'LOWER';
    const TYPE_LTRIM = 'LTRIM';
    const TYPE_RTRIM = 'RTRIM';
    const TYPE_SUBSTR = 'SUBSTR';
    const TYPE_TRIM = 'TRIM';
    const TYPE_UPPER = 'UPPER';

    protected $type;
    protected $arguments;

    public function __construct($type, $arguments)
    {
        if (count($arguments)) {
            $this->type = mb_strtoupper($type);

            $this->arguments = array_map(function($v) {
                return ValuePart::create(array($v));
            }, $arguments);
        } else {
            throw new InvalidArgumentsException();
        }
    }

    protected function getType()
    {
        return $this->type;
    }

    protected function getArguments()
    {
        return $this->arguments;
    }
}
