<?php

namespace daisywheel\db\builder;

use daisywheel\core\Entity;
use daisywheel\core\InvalidArgumentsException;

class JoinTable extends Entity
{
    const TYPE_LEFT = 'LEFT';
    const TYPE_INNER = 'INNER';
    const TYPE_RIGHT = 'RIGHT';

    protected $selectCommand = null;
    protected $type = null;
    protected $table = null;
    protected $onCondition = null;

    public function __construct($selectCommand, $type, $arguments)
    {
        $this->selectCommand = $selectCommand;
        $this->type = $type;
        $this->table = new Table($arguments);
    }

    public function on()
    {
        $this->onCondition = ExpressionPart::create(func_get_args());
        return $this->selectCommand;
    }

    protected function getType()
    {
        return $this->type;
    }

    protected function getTable()
    {
        return $this->table;
    }

    protected function getOnCondition()
    {
        return $this->onCondition;
    }
}